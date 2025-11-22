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

/* assets/fonts.twig */
class __TwigTemplate_92bc79ff14de0470359fcdd9f0ce41b2a4171063782b0ebb5a0483ecd91b84e6 extends \Twig\Template
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
        echo "  <link href=\"https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css\" rel=\"stylesheet\"/>
  <link href=\"https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap\" rel=\"stylesheet\"/>

";
    }

    public function getTemplateName()
    {
        return "assets/fonts.twig";
    }

    public function getDebugInfo()
    {
        return array (  37 => 1,);
    }

    public function getSourceContext()
    {
        return new Source("  <link href=\"https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css\" rel=\"stylesheet\"/>
  <link href=\"https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap\" rel=\"stylesheet\"/>

", "assets/fonts.twig", "/home/daniel/Documentos/pobj-slim/src/Presentation/Views/assets/fonts.twig");
    }
}
