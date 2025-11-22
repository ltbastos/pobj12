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

/* components/sections/mobile-carousel.twig */
class __TwigTemplate_912c62c08c3aa2f36c832696d4bd014ec314781ba891dc4bc421522915979493 extends \Twig\Template
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
        echo "          <div id=\"mobile-carousel\" class=\"mobile-carousel\" aria-label=\"Destaques do painel\">
            <div class=\"mobile-carousel__track\">
              <article class=\"mobile-carousel__slide is-active\" data-slide=\"0\">
                <div class=\"mobile-carousel__content\">
                  <h4 class=\"mobile-carousel__title\">Sprint PJ em foco</h4>
                  <p class=\"mobile-carousel__desc\">Veja metas e elegibilidade da campanha atual em segundos.</p>
                </div>
              </article>
              <article class=\"mobile-carousel__slide\" data-slide=\"1\">
                <div class=\"mobile-carousel__content\">
                  <h4 class=\"mobile-carousel__title\">Simuladores ágeis</h4>
                  <p class=\"mobile-carousel__desc\">Projete cenários da equipe ou individuais com ajustes rápidos.</p>
                </div>
              </article>
              <article class=\"mobile-carousel__slide\" data-slide=\"2\">
                <div class=\"mobile-carousel__content\">
                  <h4 class=\"mobile-carousel__title\">Ranking personalizado</h4>
                  <p class=\"mobile-carousel__desc\">Compare sua posição preservando os dados dos demais.</p>
                </div>
              </article>
            </div>
            <div class=\"mobile-carousel__dots\" role=\"tablist\" aria-label=\"Navegação do carrossel\">
              <button type=\"button\" class=\"mobile-carousel__dot\" data-slide-to=\"0\" aria-label=\"Ver destaque da sprint\" aria-current=\"true\"></button>
              <button type=\"button\" class=\"mobile-carousel__dot\" data-slide-to=\"1\" aria-label=\"Ver destaque dos simuladores\"></button>
              <button type=\"button\" class=\"mobile-carousel__dot\" data-slide-to=\"2\" aria-label=\"Ver destaque do ranking\"></button>
            </div>
          </div>

";
    }

    public function getTemplateName()
    {
        return "components/sections/mobile-carousel.twig";
    }

    public function getDebugInfo()
    {
        return array (  37 => 1,);
    }

    public function getSourceContext()
    {
        return new Source("          <div id=\"mobile-carousel\" class=\"mobile-carousel\" aria-label=\"Destaques do painel\">
            <div class=\"mobile-carousel__track\">
              <article class=\"mobile-carousel__slide is-active\" data-slide=\"0\">
                <div class=\"mobile-carousel__content\">
                  <h4 class=\"mobile-carousel__title\">Sprint PJ em foco</h4>
                  <p class=\"mobile-carousel__desc\">Veja metas e elegibilidade da campanha atual em segundos.</p>
                </div>
              </article>
              <article class=\"mobile-carousel__slide\" data-slide=\"1\">
                <div class=\"mobile-carousel__content\">
                  <h4 class=\"mobile-carousel__title\">Simuladores ágeis</h4>
                  <p class=\"mobile-carousel__desc\">Projete cenários da equipe ou individuais com ajustes rápidos.</p>
                </div>
              </article>
              <article class=\"mobile-carousel__slide\" data-slide=\"2\">
                <div class=\"mobile-carousel__content\">
                  <h4 class=\"mobile-carousel__title\">Ranking personalizado</h4>
                  <p class=\"mobile-carousel__desc\">Compare sua posição preservando os dados dos demais.</p>
                </div>
              </article>
            </div>
            <div class=\"mobile-carousel__dots\" role=\"tablist\" aria-label=\"Navegação do carrossel\">
              <button type=\"button\" class=\"mobile-carousel__dot\" data-slide-to=\"0\" aria-label=\"Ver destaque da sprint\" aria-current=\"true\"></button>
              <button type=\"button\" class=\"mobile-carousel__dot\" data-slide-to=\"1\" aria-label=\"Ver destaque dos simuladores\"></button>
              <button type=\"button\" class=\"mobile-carousel__dot\" data-slide-to=\"2\" aria-label=\"Ver destaque do ranking\"></button>
            </div>
          </div>

", "components/sections/mobile-carousel.twig", "/home/daniel/Documentos/pobj-slim/src/Presentation/Views/components/sections/mobile-carousel.twig");
    }
}
