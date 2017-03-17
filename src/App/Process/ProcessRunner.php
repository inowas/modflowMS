<?php

namespace App\Process;

use App\Repository\CalculationRepository;
use Symfony\Component\Process\Process;

class ProcessRunner
{
    /** @var CalculationRepository */
    protected $calculationRepository;

    /** @var  PythonProcessFactory */
    protected $processFactory;

    /** @var array $processes */
    protected $processes = [];

    /** @var int */
    protected $numberOfParallelCalculations;


    public function __construct(CalculationRepository $calculationRepository, PythonProcessFactory $processFactory, $numberOfParallelCalculations = 5)
    {
        $this->calculationRepository = $calculationRepository;
        $this->processFactory = $processFactory;
        $this->numberOfParallelCalculations = $numberOfParallelCalculations;
    }

    /**
     * @param bool $asDaemon
     */
    public function run($asDaemon = false){

        /** Reset not finished jobs */
        $this->calculationRepository->cleanup();
        echo sprintf('Waiting for Jobs.'."\r\n");

        while (1){
            $runningProcesses = 0;
            /**
             * @var string $id
             * @var Process $process
             */
            foreach ($this->processes as $id => $process){

                if ($process->isRunning()){
                    $runningProcesses++;
                    continue;
                }

                if (! $process->isRunning()){
                    if ($process->isSuccessful()){
                        $this->calculationRepository->calculationFinished($id, true, $process->getOutput());
                        echo sprintf("Process end:\r\n Message: \r\n %s", $process->getOutput());
                    } else {
                        $this->calculationRepository->calculationFinished($id, false, $process->getOutput());
                        echo sprintf("Process ended up with error:\r\n ErrorMessage: \r\n %s", $process->getErrorOutput());
                    }

                    $this->removeProcess($id);
                }
            }

            if ($runningProcesses >= $this->numberOfParallelCalculations){
                continue;
            }

            $calculationsInQueue = $this->calculationRepository->fetchAllInQueue();
            if (count($calculationsInQueue) == 0 && $asDaemon === false && $runningProcesses == 0){
                echo sprintf('There are no more jobs in the queue. Leaving...'."\r\n");
                return;
            }

            if (count($calculationsInQueue) > 0){
                echo sprintf('Got %s more Jobs.'."\r\n", count($calculationsInQueue));
            }


            foreach ($calculationsInQueue as $calculation){
                $id = $calculation['calculation_id'];

                if ($this->containsProcess($id)){
                    continue;
                }

                $process = $this->processFactory->getProcess($id);
                $process->start();
                $this->addProcess($process, $id);
                $this->calculationRepository->calculationStarted($id);
            }
        }
    }

    private function addProcess(Process $process, string $id): void
    {
        if (! $this->containsProcess($id)){
            $this->processes[$id] = $process;
        }
    }

    private function containsProcess(string $id): bool
    {
        return array_key_exists($id, $this->processes);
    }

    private function removeProcess(string $id)
    {
        unset($this->processes[$id]);
    }
}
