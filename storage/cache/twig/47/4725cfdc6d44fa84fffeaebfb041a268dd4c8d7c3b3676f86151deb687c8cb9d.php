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

/* components/filters/filters-card-header.twig */
class __TwigTemplate_0962293a5025d4a01c42d261132513fc4578379f2f1ef7a43243078e4cf477d4 extends \Twig\Template
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
        echo "      <button id=\"btn-fechar-filtros\" class=\"filters__close\" type=\"button\" aria-label=\"Fechar filtros\">
        <i class=\"ti ti-x\" aria-hidden=\"true\"></i>
      </button>
      <header class=\"card__header\">
        <div class=\"filters-hero\">
          <div class=\"title-subtitle\">
            <h2>POBJ Produções</h2>
            <p class=\"muted\">Acompanhe dados atualizados de performance.</p>
          </div>
";
        // line 10
        $this->loadTemplate("components/sections/mobile-carousel.twig", "components/filters/filters-card-header.twig", 10)->display($context);
        // line 11
        echo "        </div>
      </header>

";
    }

    public function getTemplateName()
    {
        return "components/filters/filters-card-header.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  50 => 11,  48 => 10,  37 => 1,);
    }

    public function getSourceContext()
    {
        return new Source("      <button id=\"btn-fechar-filtros\" class=\"filters__close\" type=\"button\" aria-label=\"Fechar filtros\">
        <i class=\"ti ti-x\" aria-hidden=\"true\"></i>
      </button>
      <header class=\"card__header\">
        <div class=\"filters-hero\">
          <div class=\"title-subtitle\">
            <h2>POBJ Produções</h2>
            <p class=\"muted\">Acompanhe dados atualizados de performance.</p>
          </div>
{% include 'components/sections/mobile-carousel.twig' %}
        </div>
      </header>

", "components/filters/filters-card-header.twig", "/home/daniel/Documentos/pobj-slim/src/Presentation/Views/components/filters/filters-card-header.twig");
    }
}
