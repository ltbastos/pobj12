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

/* assets/styles-main.twig */
class __TwigTemplate_df86753554d40ad280a6cebc1a3edd3afa97086155a78685cbb6aacf4ac5cf2c extends \Twig\Template
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
        echo "  <link href=\"https://unpkg.com/gridjs/dist/theme/mermaid.min.css\" rel=\"stylesheet\"/>
  <link rel=\"stylesheet\" href=\"/css/style.css\"/>
  <link rel=\"stylesheet\" href=\"/css/leads.css\"/>
  <link rel=\"stylesheet\" href=\"/css/omega.css\"/>

";
    }

    public function getTemplateName()
    {
        return "assets/styles-main.twig";
    }

    public function getDebugInfo()
    {
        return array (  37 => 1,);
    }

    public function getSourceContext()
    {
        return new Source("  <link href=\"https://unpkg.com/gridjs/dist/theme/mermaid.min.css\" rel=\"stylesheet\"/>
  <link rel=\"stylesheet\" href=\"/css/style.css\"/>
  <link rel=\"stylesheet\" href=\"/css/leads.css\"/>
  <link rel=\"stylesheet\" href=\"/css/omega.css\"/>

", "assets/styles-main.twig", "/home/daniel/Documentos/pobj-slim/src/Presentation/Views/assets/styles-main.twig");
    }
}
