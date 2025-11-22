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

/* components/sections/tabs-navigation.twig */
class __TwigTemplate_0d72b523e9503cc49cc4f738208060009392985879948f89c9691235f29e18dd extends \Twig\Template
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
        echo "    <!-- Aqui eu montei o controle de abas principais -->
    <section class=\"tabs\" aria-label=\"Navegação principal\">
      <button class=\"tab is-active\" data-view=\"cards\" aria-label=\"Resumo\">
        <span class=\"tab-icon\"><i class=\"ti ti-dashboard\" aria-hidden=\"true\"></i></span>
        <span class=\"tab-label\">Resumo</span>
      </button>
      <button class=\"tab\" data-view=\"table\" aria-label=\"Detalhamento\">
        <span class=\"tab-icon\"><i class=\"ti ti-list-tree\" aria-hidden=\"true\"></i></span>
        <span class=\"tab-label\">Detalhes</span>
      </button>
      <button class=\"tab\" data-view=\"ranking\" aria-label=\"Rankings\">
        <span class=\"tab-icon\"><i class=\"ti ti-trophy\" aria-hidden=\"true\"></i></span>
        <span class=\"tab-label\">Rankings</span>
      </button>
      <button class=\"tab\" data-view=\"exec\" aria-label=\"Visão executiva\">
        <span class=\"tab-icon\"><i class=\"ti ti-chart-line\" aria-hidden=\"true\"></i></span>
        <span class=\"tab-label\">Visão executiva</span>
      </button>
      <button class=\"tab\" data-view=\"simuladores\" aria-label=\"Simuladores\">
        <span class=\"tab-icon\"><i class=\"ti ti-calculator\" aria-hidden=\"true\"></i></span>
        <span class=\"tab-label\">Simuladores</span>
      </button>
      <button class=\"tab\" data-view=\"campanhas\" aria-label=\"Campanhas\">
        <span class=\"tab-icon\"><i class=\"ti ti-speakerphone\" aria-hidden=\"true\"></i></span>
        <span class=\"tab-label\">Campanhas</span>
      </button>
      <div class=\"tabs__aside\">
        <small class=\"muted\"><span id=\"lbl-atualizacao\"></span></small>
      </div>
    </section>

";
    }

    public function getTemplateName()
    {
        return "components/sections/tabs-navigation.twig";
    }

    public function getDebugInfo()
    {
        return array (  37 => 1,);
    }

    public function getSourceContext()
    {
        return new Source("    <!-- Aqui eu montei o controle de abas principais -->
    <section class=\"tabs\" aria-label=\"Navegação principal\">
      <button class=\"tab is-active\" data-view=\"cards\" aria-label=\"Resumo\">
        <span class=\"tab-icon\"><i class=\"ti ti-dashboard\" aria-hidden=\"true\"></i></span>
        <span class=\"tab-label\">Resumo</span>
      </button>
      <button class=\"tab\" data-view=\"table\" aria-label=\"Detalhamento\">
        <span class=\"tab-icon\"><i class=\"ti ti-list-tree\" aria-hidden=\"true\"></i></span>
        <span class=\"tab-label\">Detalhes</span>
      </button>
      <button class=\"tab\" data-view=\"ranking\" aria-label=\"Rankings\">
        <span class=\"tab-icon\"><i class=\"ti ti-trophy\" aria-hidden=\"true\"></i></span>
        <span class=\"tab-label\">Rankings</span>
      </button>
      <button class=\"tab\" data-view=\"exec\" aria-label=\"Visão executiva\">
        <span class=\"tab-icon\"><i class=\"ti ti-chart-line\" aria-hidden=\"true\"></i></span>
        <span class=\"tab-label\">Visão executiva</span>
      </button>
      <button class=\"tab\" data-view=\"simuladores\" aria-label=\"Simuladores\">
        <span class=\"tab-icon\"><i class=\"ti ti-calculator\" aria-hidden=\"true\"></i></span>
        <span class=\"tab-label\">Simuladores</span>
      </button>
      <button class=\"tab\" data-view=\"campanhas\" aria-label=\"Campanhas\">
        <span class=\"tab-icon\"><i class=\"ti ti-speakerphone\" aria-hidden=\"true\"></i></span>
        <span class=\"tab-label\">Campanhas</span>
      </button>
      <div class=\"tabs__aside\">
        <small class=\"muted\"><span id=\"lbl-atualizacao\"></span></small>
      </div>
    </section>

", "components/sections/tabs-navigation.twig", "/home/daniel/Documentos/pobj-slim/src/Presentation/Views/components/sections/tabs-navigation.twig");
    }
}
