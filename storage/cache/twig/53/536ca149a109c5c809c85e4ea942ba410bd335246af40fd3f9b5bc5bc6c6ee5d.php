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

/* components/filters/filters-advanced.twig */
class __TwigTemplate_06581ba2fd7315107bf53c3ab119cfefb05080acc4082d706e8434f8243116c5 extends \Twig\Template
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
        echo "      <!-- Aqui eu deixei os filtros avançados escondidos no slider -->
      <div id=\"advanced-filters\" class=\"adv\" aria-hidden=\"true\">
        <div class=\"adv__grid\">
          <div class=\"filters__group\">
            <label>Agência</label>
            <select id=\"f-agencia\" class=\"input\" data-search=\"true\"></select>
          </div>
          <div class=\"filters__group\">
            <label>Gerente de gestão</label>
            <select id=\"f-gerente-gestao\" class=\"input\" data-search=\"true\"></select>
          </div>
          <div class=\"filters__group\">
            <label>Família</label>
            <select id=\"f-secao\" class=\"input\" data-search=\"true\"></select>
          </div>
          <div class=\"filters__group\">
            <label>Indicadores</label>
            <select id=\"f-familia\" class=\"input\" data-search=\"true\"></select>
          </div>
          <!-- Aqui eu removi o grupo de produtos porque virou redundante -->
          <div class=\"filters__group\">
            <label>Subindicador</label>
            <select id=\"f-produto\" class=\"input\" data-search=\"true\"></select>
          </div>
          <div class=\"filters__group\">
            <label>Status dos indicadores</label>
            <select id=\"f-status-kpi\" class=\"input\">
              <option value=\"todos\" selected>Todos</option>
              <option value=\"atingidos\">Atingidos</option>
              <option value=\"nao\">Não atingidos</option>
            </select>
          </div>
          <div class=\"filters__group\">
            <label>Visão acumulada</label>
            <select id=\"f-visao\" class=\"input\"></select>
          </div>
        </div>
      </div>

";
    }

    public function getTemplateName()
    {
        return "components/filters/filters-advanced.twig";
    }

    public function getDebugInfo()
    {
        return array (  37 => 1,);
    }

    public function getSourceContext()
    {
        return new Source("      <!-- Aqui eu deixei os filtros avançados escondidos no slider -->
      <div id=\"advanced-filters\" class=\"adv\" aria-hidden=\"true\">
        <div class=\"adv__grid\">
          <div class=\"filters__group\">
            <label>Agência</label>
            <select id=\"f-agencia\" class=\"input\" data-search=\"true\"></select>
          </div>
          <div class=\"filters__group\">
            <label>Gerente de gestão</label>
            <select id=\"f-gerente-gestao\" class=\"input\" data-search=\"true\"></select>
          </div>
          <div class=\"filters__group\">
            <label>Família</label>
            <select id=\"f-secao\" class=\"input\" data-search=\"true\"></select>
          </div>
          <div class=\"filters__group\">
            <label>Indicadores</label>
            <select id=\"f-familia\" class=\"input\" data-search=\"true\"></select>
          </div>
          <!-- Aqui eu removi o grupo de produtos porque virou redundante -->
          <div class=\"filters__group\">
            <label>Subindicador</label>
            <select id=\"f-produto\" class=\"input\" data-search=\"true\"></select>
          </div>
          <div class=\"filters__group\">
            <label>Status dos indicadores</label>
            <select id=\"f-status-kpi\" class=\"input\">
              <option value=\"todos\" selected>Todos</option>
              <option value=\"atingidos\">Atingidos</option>
              <option value=\"nao\">Não atingidos</option>
            </select>
          </div>
          <div class=\"filters__group\">
            <label>Visão acumulada</label>
            <select id=\"f-visao\" class=\"input\"></select>
          </div>
        </div>
      </div>

", "components/filters/filters-advanced.twig", "/home/daniel/Documentos/pobj-slim/src/Presentation/Views/components/filters/filters-advanced.twig");
    }
}
