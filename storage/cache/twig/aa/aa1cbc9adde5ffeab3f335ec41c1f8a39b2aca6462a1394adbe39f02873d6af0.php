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

/* components/filters/filters-backdrop.twig */
class __TwigTemplate_ff316d72820907481706287d3a1beb88c9039f62d314b977c02c0999260f0aae extends \Twig\Template
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
        echo "  <div id=\"filters-backdrop\" class=\"filters-backdrop\" hidden></div>

";
    }

    public function getTemplateName()
    {
        return "components/filters/filters-backdrop.twig";
    }

    public function getDebugInfo()
    {
        return array (  37 => 1,);
    }

    public function getSourceContext()
    {
        return new Source("  <div id=\"filters-backdrop\" class=\"filters-backdrop\" hidden></div>

", "components/filters/filters-backdrop.twig", "/home/daniel/Documentos/pobj-slim/src/Presentation/Views/components/filters/filters-backdrop.twig");
    }
}
