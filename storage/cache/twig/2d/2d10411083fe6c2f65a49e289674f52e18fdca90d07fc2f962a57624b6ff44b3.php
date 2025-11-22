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

/* components/filters/resumo-mode-toggle.twig */
class __TwigTemplate_7ad00d71c699b4d4d2d4e203e9d7e05e26203cae7b3c6cbe69139fa5ff6cd19a extends \Twig\Template
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
        echo "      <div class=\"resumo-mode\">
        <div class=\"resumo-mode__toggle\" id=\"resumo-mode-toggle\" role=\"group\" aria-label=\"Alterar visão do resumo\">
          <div class=\"segmented\">
            <button type=\"button\" class=\"seg-btn is-active\" data-mode=\"cards\" aria-pressed=\"true\">Visão por cards</button>
            <button type=\"button\" class=\"seg-btn\" data-mode=\"legacy\" aria-pressed=\"false\">Visão clássica</button>
          </div>
        </div>
      </div>

";
    }

    public function getTemplateName()
    {
        return "components/filters/resumo-mode-toggle.twig";
    }

    public function getDebugInfo()
    {
        return array (  37 => 1,);
    }

    public function getSourceContext()
    {
        return new Source("      <div class=\"resumo-mode\">
        <div class=\"resumo-mode__toggle\" id=\"resumo-mode-toggle\" role=\"group\" aria-label=\"Alterar visão do resumo\">
          <div class=\"segmented\">
            <button type=\"button\" class=\"seg-btn is-active\" data-mode=\"cards\" aria-pressed=\"true\">Visão por cards</button>
            <button type=\"button\" class=\"seg-btn\" data-mode=\"legacy\" aria-pressed=\"false\">Visão clássica</button>
          </div>
        </div>
      </div>

", "components/filters/resumo-mode-toggle.twig", "/home/daniel/Documentos/pobj-slim/src/Presentation/Views/components/filters/resumo-mode-toggle.twig");
    }
}
