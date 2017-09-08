<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use LightnCandy\LightnCandy;

class DefaultController extends Controller
{
    private $data;

    public function __construct()
    {
        $this->setData();
    }

    private function setData()
    {
        $this->data = array(
            'title' => 'John',
            'text' => 'asldknalsdasld asdj lasjd lasjd lasjd lasdj',
            'foo' => 'bar',
        );
    }

    /**
     * @Route("/{templateName}", name="homepage")
     */
    public function indexAction($templateName)
    {
        $template = $this->getTemplate($templateName);
        $phpTemplate = $this->compileTemplate($template);
        $this->cachePhpTemplate($templateName, $phpTemplate);

        $renderer = include($this->getTemplateDirectory() . $templateName . '.render.php');
        $output = $renderer($this->data);

        return new Response($output);
    }

    private function getTemplate($templateName)
    {
        return file_get_contents($this->getTemplateDirectory() . $templateName . '.handlebars');
    }

    private function getTemplateDirectory()
    {
        return dirname(__DIR__) . '/../../var/templates/';
    }

    private function compileTemplate($template)
    {
        return LightnCandy::compile($template, array(
                'partialresolver' => function ($cx, $name) {
                    if (file_exists($this->getTemplateDirectory() . "$name.handlebars")) {
                        return file_get_contents($this->getTemplateDirectory() . "$name.handlebars");
                    }
                    return "[partial (file:$name.handlebars) not found]";
                }
            )
        );
    }

    private function cachePhpTemplate($templateName, $phpTemplate)
    {
        file_put_contents($this->getTemplateDirectory() . $templateName . '.render.php', '<?php ' . $phpTemplate . '?>');
    }

    /**
     * @Route("/", name="home")
     */
    public function homeAction()
    {
        return $this->render('base.handlebars', [
            "foo" => array(
                array(
                    "template" => "text",
                    "name" => "alice"
                ),
                array(
                    "template" => "footer",
                    'company' => 'Thomas Kauft Laden',
                    'currentyear' => '2017',
                ),
                array(
                    "template" => "footer",
                    "name" => "asasass"
                ),
            ),
            'footer' => [
                'company' => 'Thomas Kauft Laden',
                'currentyear' => '2017',
            ],
        ]);
    }
}
