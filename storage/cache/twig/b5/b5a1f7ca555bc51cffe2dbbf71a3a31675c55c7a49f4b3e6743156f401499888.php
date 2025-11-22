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

/* layouts/footer.twig */
class __TwigTemplate_e18bb42d1645036bd519a48e62774db4857a81197e55c01718334e1d168534e1 extends \Twig\Template
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
        echo "  <footer class=\"footer\">
    <span>Bradesco POBJ • v1.0 </span>
  </footer>

";
    }

    public function getTemplateName()
    {
        return "layouts/footer.twig";
    }

    public function getDebugInfo()
    {
        return array (  37 => 1,);
    }

    public function getSourceContext()
    {
        return new Source("  <footer class=\"footer\">
    <span>Bradesco POBJ • v1.0 </span>
  </footer>

", "layouts/footer.twig", "/home/daniel/Documentos/pobj-slim/src/Presentation/Views/layouts/footer.twig");
    }
}
