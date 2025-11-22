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

/* layouts/scripts.twig */
class __TwigTemplate_4027f91da9c4608afa5e2f1546bf3a81648cdddd70307eda3e80567970381821 extends \Twig\Template
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
        echo "  <script>
    window.API_URL = ";
        // line 2
        echo twig_escape_filter($this->env, (((isset($context["API_URL"]) || array_key_exists("API_URL", $context))) ? (_twig_default_filter(($context["API_URL"] ?? null), "null")) : ("null")), "html", null, true);
        echo ";
    window.API_HTTP_BASE = ";
        // line 3
        echo twig_escape_filter($this->env, (((isset($context["API_HTTP_BASE"]) || array_key_exists("API_HTTP_BASE", $context))) ? (_twig_default_filter(($context["API_HTTP_BASE"] ?? null), "null")) : ("null")), "html", null, true);
        echo ";
  </script>
  <script src=\"https://cdn.jsdelivr.net/npm/papaparse@5.4.1/papaparse.min.js\"></script>
  <script src=\"https://unpkg.com/gridjs/dist/gridjs.umd.js\"></script>
  <!-- API Data Loaders -->
  <script src=\"/js/api/estrutura.js\"></script>
  <script src=\"/js/api/status.js\"></script>
  <script src=\"/js/api/produtos.js\"></script>
  <script src=\"/js/api/calendario.js\"></script>
  <script src=\"/js/api/realizados.js\"></script>
  <script src=\"/js/api/metas.js\"></script>
  <script src=\"/js/api/variavel.js\"></script>
  <script src=\"/js/api/mesu.js\"></script>
  <script src=\"/js/api/campanhas.js\"></script>
  <script src=\"/js/api/detalhes.js\"></script>
  <script src=\"/js/api/historico.js\"></script>
  <script src=\"/js/api/leads.js\"></script>
  
  <!-- Core Application -->
  <script src=\"/js/core/app.js\"></script>
  
  <!-- Features -->
  <script src=\"/js/features/opportunities.js\"></script>
  <script src=\"/js/features/omega.js\"></script>

";
    }

    public function getTemplateName()
    {
        return "layouts/scripts.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  44 => 3,  40 => 2,  37 => 1,);
    }

    public function getSourceContext()
    {
        return new Source("  <script>
    window.API_URL = {{ API_URL|default('null') }};
    window.API_HTTP_BASE = {{ API_HTTP_BASE|default('null') }};
  </script>
  <script src=\"https://cdn.jsdelivr.net/npm/papaparse@5.4.1/papaparse.min.js\"></script>
  <script src=\"https://unpkg.com/gridjs/dist/gridjs.umd.js\"></script>
  <!-- API Data Loaders -->
  <script src=\"/js/api/estrutura.js\"></script>
  <script src=\"/js/api/status.js\"></script>
  <script src=\"/js/api/produtos.js\"></script>
  <script src=\"/js/api/calendario.js\"></script>
  <script src=\"/js/api/realizados.js\"></script>
  <script src=\"/js/api/metas.js\"></script>
  <script src=\"/js/api/variavel.js\"></script>
  <script src=\"/js/api/mesu.js\"></script>
  <script src=\"/js/api/campanhas.js\"></script>
  <script src=\"/js/api/detalhes.js\"></script>
  <script src=\"/js/api/historico.js\"></script>
  <script src=\"/js/api/leads.js\"></script>
  
  <!-- Core Application -->
  <script src=\"/js/core/app.js\"></script>
  
  <!-- Features -->
  <script src=\"/js/features/opportunities.js\"></script>
  <script src=\"/js/features/omega.js\"></script>

", "layouts/scripts.twig", "/home/daniel/Documentos/pobj-slim/src/Presentation/Views/layouts/scripts.twig");
    }
}
