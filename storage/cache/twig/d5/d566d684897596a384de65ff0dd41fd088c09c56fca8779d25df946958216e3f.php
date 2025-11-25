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

/* components/filters/filters-basic.twig */
class __TwigTemplate_6fc4d1928fd0284241fe2646f263566e65820272218c7b6feb38570530dd0e2c extends \Twig\Template
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
        echo "      <div class=\"filters\">
        <div class=\"filters__group\">
          <label>Diretoria</label>
          <select id=\"f-diretoria\" class=\"input\" data-search=\"true\"></select>
        </div>
        <div class=\"filters__group\">
          <label>Regional</label>
          <select id=\"f-gerencia\" class=\"input\" data-search=\"true\"></select>
        </div>
        <div class=\"filters__group\">
          <label>Gerente</label>
          <select id=\"f-gerente\" class=\"input\" data-search=\"true\"></select>
        </div>
        <div class=\"filters__actions\">
          <button id=\"btn-filtrar\" class=\"btn btn--primary\"><i class=\"ti ti-search\"></i> Filtrar</button>
          <button id=\"btn-limpar\" class=\"btn\"><i class=\"ti ti-x\"></i> Limpar filtros</button>
        </div>
      </div>

      <div class=\"filters__more filters__more--center\">
        <button id=\"btn-abrir-filtros\" class=\"btn btn--link btn--info\" aria-expanded=\"false\">
          <i class=\"ti ti-chevron-down\"></i> Abrir filtros
        </button>
      </div>

";
    }

    public function getTemplateName()
    {
        return "components/filters/filters-basic.twig";
    }

    public function getDebugInfo()
    {
        return array (  37 => 1,);
    }

    public function getSourceContext()
    {
        return new Source("      <div class=\"filters\">
        <div class=\"filters__group\">
          <label>Diretoria</label>
          <select id=\"f-diretoria\" class=\"input\" data-search=\"true\"></select>
        </div>
        <div class=\"filters__group\">
          <label>Regional</label>
          <select id=\"f-gerencia\" class=\"input\" data-search=\"true\"></select>
        </div>
        <div class=\"filters__group\">
          <label>Gerente</label>
          <select id=\"f-gerente\" class=\"input\" data-search=\"true\"></select>
        </div>
        <div class=\"filters__actions\">
          <button id=\"btn-filtrar\" class=\"btn btn--primary\"><i class=\"ti ti-search\"></i> Filtrar</button>
          <button id=\"btn-limpar\" class=\"btn\"><i class=\"ti ti-x\"></i> Limpar filtros</button>
        </div>
      </div>

      <div class=\"filters__more filters__more--center\">
        <button id=\"btn-abrir-filtros\" class=\"btn btn--link btn--info\" aria-expanded=\"false\">
          <i class=\"ti ti-chevron-down\"></i> Abrir filtros
        </button>
      </div>

", "components/filters/filters-basic.twig", "/home/daniel/Documentos/pobj-slim/src/Presentation/Views/components/filters/filters-basic.twig");
    }
}
