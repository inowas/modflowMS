<?php

namespace App\Controller;

use Assert\Assertion;
use Ramsey\Uuid\Uuid;
use Silex\Application;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class ApiController
{

    /** @var Application */
    private $app;

    /**
     * ApiController constructor.
     * @param $app
     */
    public function __construct($app)
    {
        $this->app = $app;
    }

    /**
     * @param Request $request
     * @param string $id
     * @return JsonResponse
     */
    public function postCalculateAction(Request $request, ?string $id)
    {
        $name = $this->app['uploaded_file_name'];
        $modelsPath = $this->app['models.path'];

        /** @var \Symfony\Component\HttpFoundation\File\UploadedFile $configFile */
        $configFile = $request->files->get('file');
        if (! $configFile->isValid()){
            return new JsonResponse('Uploaded file is not valid');
        }

        $uuid = Uuid::uuid4();
        if ($id && Uuid::isValid($id)){
            $uuid = Uuid::fromString($id);
        }

        $configFile->move($modelsPath.'/'.$uuid->toString(), $name);
        $filename = $modelsPath.'/'.$uuid->toString().'/'.$name;

        if (file_exists($filename)){
            $content = file_get_contents($filename);
            Assertion::isJsonString($content);
            return new JsonResponse($content);
        }

        return new JsonResponse('Something went wrong.');
    }

    /**
     * @return JsonResponse
     */
    public function postValidateAction()
    {
        return new JsonResponse('Thanks');
    }
}
