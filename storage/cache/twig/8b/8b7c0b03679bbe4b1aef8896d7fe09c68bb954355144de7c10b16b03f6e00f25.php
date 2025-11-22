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

/* components/modals/leads-modal.twig */
class __TwigTemplate_1bb4c7cc9371d931905e2fed0181d1074afccd29f69dad74a68ae1684814c804 extends \Twig\Template
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
        echo "<div id=\"leads-modal\" class=\"leads-modal\" hidden aria-hidden=\"true\">
  <div class=\"leads-modal__overlay\" data-leads-close></div>
  <div class=\"leads-modal__panel\" role=\"dialog\" aria-modal=\"true\" aria-labelledby=\"leads-modal-title\" aria-describedby=\"leads-modal-subtitle\">
    <header class=\"leads-modal__head\">
      <div>
        <span class=\"leads-modal__eyebrow\">Oportunidades</span>
        <h4 id=\"leads-modal-title\">Leads propensos</h4>
        <p id=\"leads-modal-subtitle\" class=\"leads-modal__subtitle\"></p>
      </div>
      <button type=\"button\" class=\"icon-btn leads-modal__close\" data-leads-close aria-label=\"Fechar painel de leads\">
        <i class=\"ti ti-x\"></i>
      </button>
    </header>
    <section class=\"leads-modal__filters\">
      <label for=\"leads-product-filter\" class=\"leads-modal__filter-label\">Subindicador</label>
      <select id=\"leads-product-filter\" class=\"input leads-modal__filter-select\">
        <option value=\"\">Todos os subindicadores</option>
      </select>
    </section>
    <section id=\"leads-modal-summary\" class=\"leads-modal__summary\"></section>
    <section class=\"leads-modal__body\">
      <div class=\"leads-modal__grid\">
        <div id=\"leads-modal-list\" class=\"leads-modal__table\"></div>
        <aside id=\"lead-detail-panel\" class=\"lead-detail\" aria-live=\"polite\">
          <p class=\"lead-detail__empty\">Selecione um lead para ver o histórico e os detalhes completos.</p>
        </aside>
      </div>
      <aside id=\"lead-contact-drawer\" class=\"lead-contact-drawer\" aria-hidden=\"true\" aria-labelledby=\"lead-contact-company\" role=\"dialog\">
        <div class=\"lead-contact-drawer__content\">
          <header class=\"lead-contact-drawer__head\">
            <div class=\"lead-contact-drawer__titles\">
              <p class=\"lead-contact-drawer__eyebrow\">Registrar contato</p>
              <h5 id=\"lead-contact-company\">Empresa selecionada</h5>
              <p id=\"lead-contact-product\" class=\"lead-contact-drawer__product\"></p>
            </div>
            <button type=\"button\" class=\"icon-btn lead-contact-drawer__close\" data-lead-contact-cancel aria-label=\"Fechar formulário de contato\">
              <i class=\"ti ti-x\"></i>
            </button>
          </header>
          <div class=\"lead-contact-drawer__meta\">
            <div class=\"lead-contact-drawer__meta-item\">
              <span class=\"lead-contact-drawer__label\">Crédito pré-aprovado</span>
              <strong id=\"lead-contact-credit\">—</strong>
            </div>
            <div class=\"lead-contact-drawer__meta-item\">
              <span class=\"lead-contact-drawer__label\">Origem</span>
              <span id=\"lead-contact-origin\" class=\"lead-origin-badge\">—</span>
            </div>
          </div>
          <div id=\"lead-contact-context\" class=\"lead-contact-drawer__context\"></div>
          <form id=\"lead-contact-form\" class=\"lead-contact-form\">
            <input type=\"hidden\" id=\"lead-contact-id\" name=\"leadId\" />
            <div class=\"lead-contact-form__field\">
              <label for=\"lead-contact-date\">Data do contato</label>
              <input type=\"date\" id=\"lead-contact-date\" name=\"date\" class=\"input\" required />
            </div>
            <div class=\"lead-contact-form__field\">
              <label for=\"lead-contact-responsavel\">Responsável pelo contato</label>
              <input type=\"text\" id=\"lead-contact-responsavel\" name=\"responsavel\" class=\"input\" readonly />
            </div>
            <div class=\"lead-contact-form__field\">
              <label for=\"lead-contact-comment\">Comentário</label>
              <textarea id=\"lead-contact-comment\" name=\"comentario\" class=\"input\" rows=\"4\" placeholder=\"Compartilhe observações relevantes\"></textarea>
            </div>
            <div class=\"lead-contact-form__actions\">
              <button type=\"button\" class=\"btn\" data-lead-contact-cancel>Cancelar</button>
              <button type=\"submit\" class=\"btn btn--primary\">Salvar contato</button>
            </div>
          </form>
        </div>
      </aside>
    </section>
  </div>
</div>

";
    }

    public function getTemplateName()
    {
        return "components/modals/leads-modal.twig";
    }

    public function getDebugInfo()
    {
        return array (  37 => 1,);
    }

    public function getSourceContext()
    {
        return new Source("<div id=\"leads-modal\" class=\"leads-modal\" hidden aria-hidden=\"true\">
  <div class=\"leads-modal__overlay\" data-leads-close></div>
  <div class=\"leads-modal__panel\" role=\"dialog\" aria-modal=\"true\" aria-labelledby=\"leads-modal-title\" aria-describedby=\"leads-modal-subtitle\">
    <header class=\"leads-modal__head\">
      <div>
        <span class=\"leads-modal__eyebrow\">Oportunidades</span>
        <h4 id=\"leads-modal-title\">Leads propensos</h4>
        <p id=\"leads-modal-subtitle\" class=\"leads-modal__subtitle\"></p>
      </div>
      <button type=\"button\" class=\"icon-btn leads-modal__close\" data-leads-close aria-label=\"Fechar painel de leads\">
        <i class=\"ti ti-x\"></i>
      </button>
    </header>
    <section class=\"leads-modal__filters\">
      <label for=\"leads-product-filter\" class=\"leads-modal__filter-label\">Subindicador</label>
      <select id=\"leads-product-filter\" class=\"input leads-modal__filter-select\">
        <option value=\"\">Todos os subindicadores</option>
      </select>
    </section>
    <section id=\"leads-modal-summary\" class=\"leads-modal__summary\"></section>
    <section class=\"leads-modal__body\">
      <div class=\"leads-modal__grid\">
        <div id=\"leads-modal-list\" class=\"leads-modal__table\"></div>
        <aside id=\"lead-detail-panel\" class=\"lead-detail\" aria-live=\"polite\">
          <p class=\"lead-detail__empty\">Selecione um lead para ver o histórico e os detalhes completos.</p>
        </aside>
      </div>
      <aside id=\"lead-contact-drawer\" class=\"lead-contact-drawer\" aria-hidden=\"true\" aria-labelledby=\"lead-contact-company\" role=\"dialog\">
        <div class=\"lead-contact-drawer__content\">
          <header class=\"lead-contact-drawer__head\">
            <div class=\"lead-contact-drawer__titles\">
              <p class=\"lead-contact-drawer__eyebrow\">Registrar contato</p>
              <h5 id=\"lead-contact-company\">Empresa selecionada</h5>
              <p id=\"lead-contact-product\" class=\"lead-contact-drawer__product\"></p>
            </div>
            <button type=\"button\" class=\"icon-btn lead-contact-drawer__close\" data-lead-contact-cancel aria-label=\"Fechar formulário de contato\">
              <i class=\"ti ti-x\"></i>
            </button>
          </header>
          <div class=\"lead-contact-drawer__meta\">
            <div class=\"lead-contact-drawer__meta-item\">
              <span class=\"lead-contact-drawer__label\">Crédito pré-aprovado</span>
              <strong id=\"lead-contact-credit\">—</strong>
            </div>
            <div class=\"lead-contact-drawer__meta-item\">
              <span class=\"lead-contact-drawer__label\">Origem</span>
              <span id=\"lead-contact-origin\" class=\"lead-origin-badge\">—</span>
            </div>
          </div>
          <div id=\"lead-contact-context\" class=\"lead-contact-drawer__context\"></div>
          <form id=\"lead-contact-form\" class=\"lead-contact-form\">
            <input type=\"hidden\" id=\"lead-contact-id\" name=\"leadId\" />
            <div class=\"lead-contact-form__field\">
              <label for=\"lead-contact-date\">Data do contato</label>
              <input type=\"date\" id=\"lead-contact-date\" name=\"date\" class=\"input\" required />
            </div>
            <div class=\"lead-contact-form__field\">
              <label for=\"lead-contact-responsavel\">Responsável pelo contato</label>
              <input type=\"text\" id=\"lead-contact-responsavel\" name=\"responsavel\" class=\"input\" readonly />
            </div>
            <div class=\"lead-contact-form__field\">
              <label for=\"lead-contact-comment\">Comentário</label>
              <textarea id=\"lead-contact-comment\" name=\"comentario\" class=\"input\" rows=\"4\" placeholder=\"Compartilhe observações relevantes\"></textarea>
            </div>
            <div class=\"lead-contact-form__actions\">
              <button type=\"button\" class=\"btn\" data-lead-contact-cancel>Cancelar</button>
              <button type=\"submit\" class=\"btn btn--primary\">Salvar contato</button>
            </div>
          </form>
        </div>
      </aside>
    </section>
  </div>
</div>

", "components/modals/leads-modal.twig", "/home/daniel/Documentos/pobj-slim/src/Presentation/Views/components/modals/leads-modal.twig");
    }
}
