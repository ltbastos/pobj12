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

/* components/sections/detail-designer.twig */
class __TwigTemplate_5b6f5acce3ca7507c672c8a76fe8dc04cb55bae27450fa33f0d97d483c837262 extends \Twig\Template
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
        echo "  <div id=\"detail-designer\" class=\"detail-designer\" hidden aria-hidden=\"true\">
    <div class=\"detail-designer__overlay\" data-designer-close></div>
    <div class=\"detail-designer__panel\" role=\"dialog\" aria-modal=\"true\" aria-labelledby=\"detail-designer-title\">
      <header class=\"detail-designer__head\">
        <div>
          <h4 id=\"detail-designer-title\">Personalizar colunas</h4>
          <p class=\"detail-designer__subtitle\">Arraste as colunas para montar a visão da tabela e salve até 5 configurações preferidas. A coluna de ações continua fixa no final da grade.</p>
        </div>
        <button type=\"button\" class=\"icon-btn detail-designer__close\" data-designer-close aria-label=\"Fechar personalização\">
          <i class=\"ti ti-x\"></i>
        </button>
      </header>
      <div id=\"detail-designer-feedback\" class=\"detail-designer__feedback\" role=\"alert\" hidden></div>
      <section class=\"detail-designer__views\">
        <div class=\"detail-designer__views-head\">
          <span>Visões salvas</span>
          <small>Carregue para ajustar ou excluir.</small>
        </div>
        <div id=\"detail-designer-views\" class=\"detail-view-chips detail-view-chips--designer\"></div>
      </section>
      <section class=\"detail-designer__lists\">
        <div class=\"detail-designer__list detail-designer__list--available\">
          <div class=\"detail-designer__list-head\">
            <h5>Colunas disponíveis</h5>
            <p>Arraste para incluir ou clique para adicionar.</p>
          </div>
          <div class=\"detail-designer__items\" data-items=\"available\"></div>
        </div>
        <div class=\"detail-designer__list detail-designer__list--selected\">
          <div class=\"detail-designer__list-head\">
            <h5>Colunas na tabela</h5>
            <p>Arraste para reorganizar ou clique para remover.</p>
          </div>
          <div class=\"detail-designer__items\" data-items=\"selected\"></div>
        </div>
      </section>
      <footer class=\"detail-designer__foot\">
        <div class=\"detail-designer__save\">
          <label for=\"detail-view-name\">Salvar nova visão</label>
          <div class=\"detail-designer__save-controls\">
            <input type=\"text\" id=\"detail-view-name\" class=\"input input--sm\" maxlength=\"48\" placeholder=\"Ex.: Indicadores priorizados\" />
            <button type=\"button\" id=\"detail-save-view\" class=\"btn btn--primary btn--sm\">Salvar visão</button>
          </div>
          <small id=\"detail-save-hint\" class=\"muted\">Você pode guardar até 5 visões personalizadas.</small>
        </div>
        <div class=\"detail-designer__actions\">
          <button type=\"button\" class=\"btn btn--ghost btn--sm\" data-designer-close>Cancelar</button>
          <button type=\"button\" id=\"detail-apply-columns\" class=\"btn btn--sm btn--primary\">Aplicar</button>
        </div>
      </footer>
    </div>
  </div>

";
    }

    public function getTemplateName()
    {
        return "components/sections/detail-designer.twig";
    }

    public function getDebugInfo()
    {
        return array (  37 => 1,);
    }

    public function getSourceContext()
    {
        return new Source("  <div id=\"detail-designer\" class=\"detail-designer\" hidden aria-hidden=\"true\">
    <div class=\"detail-designer__overlay\" data-designer-close></div>
    <div class=\"detail-designer__panel\" role=\"dialog\" aria-modal=\"true\" aria-labelledby=\"detail-designer-title\">
      <header class=\"detail-designer__head\">
        <div>
          <h4 id=\"detail-designer-title\">Personalizar colunas</h4>
          <p class=\"detail-designer__subtitle\">Arraste as colunas para montar a visão da tabela e salve até 5 configurações preferidas. A coluna de ações continua fixa no final da grade.</p>
        </div>
        <button type=\"button\" class=\"icon-btn detail-designer__close\" data-designer-close aria-label=\"Fechar personalização\">
          <i class=\"ti ti-x\"></i>
        </button>
      </header>
      <div id=\"detail-designer-feedback\" class=\"detail-designer__feedback\" role=\"alert\" hidden></div>
      <section class=\"detail-designer__views\">
        <div class=\"detail-designer__views-head\">
          <span>Visões salvas</span>
          <small>Carregue para ajustar ou excluir.</small>
        </div>
        <div id=\"detail-designer-views\" class=\"detail-view-chips detail-view-chips--designer\"></div>
      </section>
      <section class=\"detail-designer__lists\">
        <div class=\"detail-designer__list detail-designer__list--available\">
          <div class=\"detail-designer__list-head\">
            <h5>Colunas disponíveis</h5>
            <p>Arraste para incluir ou clique para adicionar.</p>
          </div>
          <div class=\"detail-designer__items\" data-items=\"available\"></div>
        </div>
        <div class=\"detail-designer__list detail-designer__list--selected\">
          <div class=\"detail-designer__list-head\">
            <h5>Colunas na tabela</h5>
            <p>Arraste para reorganizar ou clique para remover.</p>
          </div>
          <div class=\"detail-designer__items\" data-items=\"selected\"></div>
        </div>
      </section>
      <footer class=\"detail-designer__foot\">
        <div class=\"detail-designer__save\">
          <label for=\"detail-view-name\">Salvar nova visão</label>
          <div class=\"detail-designer__save-controls\">
            <input type=\"text\" id=\"detail-view-name\" class=\"input input--sm\" maxlength=\"48\" placeholder=\"Ex.: Indicadores priorizados\" />
            <button type=\"button\" id=\"detail-save-view\" class=\"btn btn--primary btn--sm\">Salvar visão</button>
          </div>
          <small id=\"detail-save-hint\" class=\"muted\">Você pode guardar até 5 visões personalizadas.</small>
        </div>
        <div class=\"detail-designer__actions\">
          <button type=\"button\" class=\"btn btn--ghost btn--sm\" data-designer-close>Cancelar</button>
          <button type=\"button\" id=\"detail-apply-columns\" class=\"btn btn--sm btn--primary\">Aplicar</button>
        </div>
      </footer>
    </div>
  </div>

", "components/sections/detail-designer.twig", "/home/daniel/Documentos/pobj-slim/src/Presentation/Views/components/sections/detail-designer.twig");
    }
}
