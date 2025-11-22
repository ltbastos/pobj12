<?php

use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Extension\SandboxExtension;
use Twig\Markup;
use Twig\Sandbox\SecurityError;
use Twig\Sandbox\SecurityNotAllowedTagError;
use Twig\Sandbox\SecurityNotAllowedFilterError;
use Twig\Sandbox\SecurityNotAllowedFunctionError;
use Twig\Source;
use Twig\Template;

/* assets/favicon.twig */
class __TwigTemplate_7908dee8a6f48aa6f57eaf73e669b3de0b44207d644d0f2cb401e80bf0a3e9fa extends \Twig\Template
{
    private $source;
    private $macros = [];

    public function __construct(Environment $env)
    {
        parent::__construct($env);

        $this->source = $this->getSourceContext();

        $this->parent = false;

        $this->blocks = [
        ];
    }

    protected function doDisplay(array $context, array $blocks = [])
    {
        $macros = $this->macros;
        // line 1
        echo "  <link rel=\"icon\" type=\"image/svg+xml\" href=\"/img/pobj.svg\">
  <link rel=\"apple-touch-icon\" href=\"/img/pobj.svg\">
  <link rel=\"shortcut icon\" href=\"/img/pobj.svg\">

";
    }

    public function getTemplateName()
    {
        return "assets/favicon.twig";
    }

    public function getDebugInfo()
    {
        return array (  37 => 1,);
    }

    public function getSourceContext()
    {
        return new Source("  <link rel=\"icon\" type=\"image/svg+xml\" href=\"/img/pobj.svg\">
  <link rel=\"apple-touch-icon\" href=\"/img/pobj.svg\">
  <link rel=\"shortcut icon\" href=\"/img/pobj.svg\">

", "assets/favicon.twig", "/home/daniel/Documentos/pobj-slim/src/Presentation/Views/assets/favicon.twig");
    }
}
