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

/* index.twig */
class __TwigTemplate_60eedaf8344e69506c63949f3c2b87f0a0dc3af2542b38a5267028c5a77d1b7d extends \Twig\Template
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
        echo "<!DOCTYPE html>
<html lang=\"pt-BR\">
<head>
";
        // line 4
        $this->loadTemplate("layouts/meta.twig", "index.twig", 4)->display($context);
        // line 5
        echo "  <title>POBJ Produções</title>
";
        // line 6
        $this->loadTemplate("assets/favicon.twig", "index.twig", 6)->display($context);
        // line 7
        $this->loadTemplate("assets/fonts.twig", "index.twig", 7)->display($context);
        // line 8
        $this->loadTemplate("assets/styles-main.twig", "index.twig", 8)->display($context);
        // line 9
        echo "</head>
<body>
";
        // line 11
        $this->loadTemplate("layouts/header-topbar.twig", "index.twig", 11)->display($context);
        // line 12
        echo "

  <main class=\"container\">
    <!-- Aqui eu organizei o card com todos os filtros principais -->
    <section class=\"card card--filters\">
";
        // line 17
        $this->loadTemplate("components/filters/filters-card-header.twig", "index.twig", 17)->display($context);
        // line 18
        echo "
";
        // line 19
        $this->loadTemplate("components/filters/filters-basic.twig", "index.twig", 19)->display($context);
        // line 20
        $this->loadTemplate("components/filters/filters-advanced.twig", "index.twig", 20)->display($context);
        // line 21
        echo "    </section>

";
        // line 23
        $this->loadTemplate("components/sections/tabs-navigation.twig", "index.twig", 23)->display($context);
        // line 24
        echo "
    <!-- Aqui eu apresento a visão de resumo com opção de cards ou tabela clássica -->
    <section id=\"view-cards\">
";
        // line 27
        $this->loadTemplate("components/filters/resumo-mode-toggle.twig", "index.twig", 27)->display($context);
        // line 28
        echo "
      <div id=\"resumo-summary\" class=\"resumo-summary\">
        <div id=\"kpi-summary\" class=\"kpi-summary\"></div>
      </div>

      <div id=\"resumo-cards\" class=\"resumo-mode__view\">
        <div id=\"grid-familias\" class=\"cards-grid\"></div>
      </div>

      <div id=\"resumo-legacy\" class=\"resumo-mode__view hidden\" aria-live=\"polite\"></div>
    </section>

    <!-- Aqui eu apresento a visão de detalhamento com a tabela em árvore -->
    <section id=\"view-table\" class=\"hidden\">
      <section id=\"table-section\" class=\"card card--table\">
        <header class=\"card__header\">
          <h3>Detalhamento</h3>
          <div class=\"card__actions\">
            <div class=\"card__search-autocomplete\">
              <input id=\"busca\" class=\"input input--sm\" type=\"search\" placeholder=\"Contrato (Ex.: CT-2025-001234)\" autocomplete=\"off\" aria-autocomplete=\"list\" aria-expanded=\"false\" aria-owns=\"contract-suggest\"/>
              <div id=\"contract-suggest\" class=\"contract-suggest\" role=\"listbox\" aria-label=\"Sugestões de contratos\" hidden></div>
            </div>
          </div>
        </header>
        <div id=\"gridRanking\"></div>
      </section>
    </section>

    <!-- Aqui eu apresento a visão executiva com gráficos e rankings -->
    <section id=\"view-exec\" class=\"hidden view-panel\">
      <section class=\"card card--exec\">
        <header class=\"card__header exec-head\">
          <div class=\"title-subtitle\">
            <h3>Visão executiva</h3>
            <div class=\"muted\" id=\"exec-context\"></div>
          </div>
          <div class=\"exec-head__actions\">
            <button id=\"btn-export-onepage\" class=\"btn btn--ghost btn--sm\" type=\"button\">
              <i class=\"ti ti-file-type-pdf\" aria-hidden=\"true\"></i>
              <span>Exportar PDF</span>
            </button>
          </div>
        </header>

        <div id=\"exec-kpis\" class=\"exec-kpis\"></div>

        <section class=\"exec-panel exec-chart\">
          <div class=\"exec-h exec-h--chart\">
            <span id=\"exec-chart-title\">Evolução mensal por seção</span>
          </div>
          <div id=\"exec-chart\" class=\"chart\" role=\"img\" aria-label=\"Linhas mensais de atingimento por seção\"></div>
          <div id=\"exec-chart-legend\" class=\"chart-legend\"></div>
        </section>

        <div class=\"exec-grid\">
          <div class=\"exec-row exec-row--rank\">
            <div class=\"exec-row__title\" id=\"exec-rank-title\">Desempenho por unidade</div>
            <div class=\"exec-row__grid\">
              <section class=\"exec-panel exec-panel--rank\" id=\"exec-rank-top-panel\">
                <div class=\"exec-h\"><span class=\"exec-panel__title\">Top 5</span></div>
                <div id=\"exec-rank-top\" class=\"rank-mini\"></div>
              </section>
              <section class=\"exec-panel exec-panel--rank\" id=\"exec-rank-bottom-panel\">
                <div class=\"exec-h\"><span class=\"exec-panel__title\">Bottom 5</span></div>
                <div id=\"exec-rank-bottom\" class=\"rank-mini\"></div>
              </section>
            </div>
          </div>

          <div class=\"exec-row exec-row--status\">
            <div class=\"exec-row__title\" id=\"exec-status-title\">Status das unidades</div>
            <div class=\"exec-row__grid exec-row__grid--status\">
              <section class=\"exec-panel exec-panel--status\" data-status=\"hit\">
                <div class=\"exec-h\"><span class=\"status-card__title\">Atingidas</span></div>
                <div id=\"exec-status-hit\" class=\"list-mini\"></div>
              </section>
              <section class=\"exec-panel exec-panel--status\" data-status=\"quase\">
                <div class=\"exec-h\"><span class=\"status-card__title\">Quase lá</span></div>
                <div id=\"exec-status-quase\" class=\"list-mini\"></div>
              </section>
              <section class=\"exec-panel exec-panel--status\" data-status=\"longe\">
                <div class=\"exec-h\"><span class=\"status-card__title\">Longe</span></div>
                <div id=\"exec-status-longe\" class=\"list-mini\"></div>
              </section>
            </div>
          </div>

          <section class=\"exec-panel exec-panel--scroll\">
            <div class=\"exec-h exec-h--heatmap\">
              <span id=\"exec-heatmap-title\">Heatmap</span>
              <div class=\"segmented seg-mini\" id=\"exec-heatmap-toggle\" role=\"tablist\" aria-label=\"Modo do heatmap\">
                <button type=\"button\" class=\"seg-btn is-active\" data-hm=\"secoes\">Seções</button>
                <button type=\"button\" class=\"seg-btn\" data-hm=\"meta\">Meta</button>
              </div>
            </div>
            <div id=\"exec-heatmap\" class=\"hm\"></div>
          </section>
        </div>
      </section>
    </section>

    <!-- Aqui eu deixo a nova aba de simuladores pronta para receber conteúdo -->
    <section id=\"view-simuladores\" class=\"hidden view-panel\">
      <section class=\"card card--simuladores\">
        <header class=\"card__header\">
          <div class=\"title-subtitle\">
            <h3>Simuladores</h3>
            <p class=\"muted\">Monte cenários \"E se?\" e antecipe pontos e remuneração antes de fechar negócios.</p>
          </div>
        </header>
        <div class=\"sim-whatif\" id=\"sim-whatif\" aria-live=\"polite\">
          <div class=\"sim-whatif__intro\">
            <span class=\"sim-whatif__badge\">Beta</span>
            <p>Selecione um indicador com meta em valor ou quantidade, informe o acréscimo esperado e acompanhe o impacto instantâneo.</p>
          </div>
          <form id=\"sim-whatif-form\" class=\"sim-whatif__form\">
            <div class=\"sim-field\">
              <label for=\"sim-whatif-indicador\">Indicador</label>
              <select id=\"sim-whatif-indicador\" class=\"input\" required>
                <option value=\"\" disabled selected>Carregando indicadores…</option>
              </select>
            </div>
            <div class=\"sim-field sim-field--split\">
              <div class=\"sim-field__label\">
                <label for=\"sim-whatif-extra\">Quanto você pretende vender a mais?</label>
                <span id=\"sim-whatif-unit\" class=\"sim-field__hint\"></span>
              </div>
              <div class=\"sim-field__control\">
                <div class=\"sim-input-group\">
                  <span class=\"sim-input-group__prefix\" id=\"sim-whatif-prefix\" aria-hidden=\"true\"></span>
                  <input id=\"sim-whatif-extra\" class=\"input\" type=\"number\" inputmode=\"decimal\" min=\"0\" step=\"1\" value=\"0\" aria-describedby=\"sim-whatif-unit\"/>
                </div>
              </div>
            </div>
            <div class=\"sim-quick\" id=\"sim-whatif-shortcuts\" aria-label=\"Atalhos de simulação\"></div>
          </form>
          <section class=\"sim-whatif__results\" aria-live=\"polite\" aria-atomic=\"true\">
            <header class=\"sim-whatif__results-head\">
              <h4 id=\"sim-whatif-title\">Escolha um indicador para iniciar</h4>
              <p id=\"sim-whatif-subtitle\" class=\"muted\">Os valores consideram os filtros aplicados na visão principal.</p>
            </header>
            <div class=\"sim-whatif__cards\" id=\"sim-whatif-cards\"></div>
            <footer class=\"sim-whatif__foot\" id=\"sim-whatif-foot\"></footer>
          </section>
        </div>
      </section>
    </section>

    <!-- Aqui eu apresento a visão de campanhas e simuladores -->
    <section id=\"view-campanhas\" class=\"hidden view-panel\">
      <section class=\"card card--campanhas\">
        <header class=\"card__header camp-header\">
          <div class=\"title-subtitle\">
            <h3>Campanhas</h3>
            <p class=\"muted\" id=\"camp-cycle\"></p>
          </div>
          <div class=\"camp-header__controls\">
            <label for=\"campanha-sprint\" class=\"muted\">Selecione a campanha</label>
            <select id=\"campanha-sprint\" class=\"input input--sm\"></select>
          </div>
        </header>

        <div class=\"camp-hero\">
          <div class=\"camp-hero__info\">
            <p id=\"camp-note\"></p>
            <div class=\"camp-period camp-period--validity\">
              <i class=\"ti ti-calendar-event\" aria-hidden=\"true\"></i>
              <span id=\"camp-validity\" class=\"camp-validity muted\"></span>
            </div>
          </div>
          <div class=\"camp-hero__stats\" id=\"camp-headline\"></div>
        </div>

        <div class=\"camp-kpi-grid\" id=\"camp-kpis\"></div>
      </section>

      <section class=\"card card--camp-sims\">
        <header class=\"card__header\">
          <div class=\"title-subtitle\">
            <h4>Simuladores da sprint</h4>
            <p class=\"muted\">Projete rapidamente o resultado da equipe ou de um gerente ajustando o atingimento de cada indicador.</p>
          </div>
        </header>
        <div class=\"sim-grid\">
          <article id=\"sim-equipe\" class=\"sim-card\"></article>
          <article id=\"sim-individual\" class=\"sim-card\"></article>
        </div>
        <p class=\"sim-footnote muted\">Cada indicador respeita o intervalo de 0% a 150% para cálculo de pontos. Valores acima disso são desconsiderados.</p>
      </section>

      <section class=\"card card--camp-ranking\">
        <header class=\"card__header\">
          <div class=\"title-subtitle\">
            <h4>Ranking da campanha</h4>
            <p class=\"muted\" id=\"camp-ranking-desc\">Acompanhe a performance das regionais e a elegibilidade frente aos critérios mínimos.</p>
          </div>
        </header>
        <div id=\"camp-ranking\"></div>
      </section>
    </section>
  </main>

";
        // line 230
        $this->loadTemplate("components/sections/detail-designer.twig", "index.twig", 230)->display($context);
        // line 231
        $this->loadTemplate("components/filters/filters-backdrop.twig", "index.twig", 231)->display($context);
        // line 232
        $this->loadTemplate("components/modals/omega-modal.twig", "index.twig", 232)->display($context);
        // line 233
        $this->loadTemplate("components/modals/leads-modal.twig", "index.twig", 233)->display($context);
        // line 234
        echo "
";
        // line 235
        $this->loadTemplate("layouts/footer.twig", "index.twig", 235)->display($context);
        // line 236
        $this->loadTemplate("layouts/scripts.twig", "index.twig", 236)->display($context);
        // line 237
        echo "</body>
</html>

";
    }

    public function getTemplateName()
    {
        return "index.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  307 => 237,  305 => 236,  303 => 235,  300 => 234,  298 => 233,  296 => 232,  294 => 231,  292 => 230,  88 => 28,  86 => 27,  81 => 24,  79 => 23,  75 => 21,  73 => 20,  71 => 19,  68 => 18,  66 => 17,  59 => 12,  57 => 11,  53 => 9,  51 => 8,  49 => 7,  47 => 6,  44 => 5,  42 => 4,  37 => 1,);
    }

    public function getSourceContext()
    {
        return new Source("<!DOCTYPE html>
<html lang=\"pt-BR\">
<head>
{% include 'layouts/meta.twig' %}
  <title>POBJ Produções</title>
{% include 'assets/favicon.twig' %}
{% include 'assets/fonts.twig' %}
{% include 'assets/styles-main.twig' %}
</head>
<body>
{% include 'layouts/header-topbar.twig' %}


  <main class=\"container\">
    <!-- Aqui eu organizei o card com todos os filtros principais -->
    <section class=\"card card--filters\">
{% include 'components/filters/filters-card-header.twig' %}

{% include 'components/filters/filters-basic.twig' %}
{% include 'components/filters/filters-advanced.twig' %}
    </section>

{% include 'components/sections/tabs-navigation.twig' %}

    <!-- Aqui eu apresento a visão de resumo com opção de cards ou tabela clássica -->
    <section id=\"view-cards\">
{% include 'components/filters/resumo-mode-toggle.twig' %}

      <div id=\"resumo-summary\" class=\"resumo-summary\">
        <div id=\"kpi-summary\" class=\"kpi-summary\"></div>
      </div>

      <div id=\"resumo-cards\" class=\"resumo-mode__view\">
        <div id=\"grid-familias\" class=\"cards-grid\"></div>
      </div>

      <div id=\"resumo-legacy\" class=\"resumo-mode__view hidden\" aria-live=\"polite\"></div>
    </section>

    <!-- Aqui eu apresento a visão de detalhamento com a tabela em árvore -->
    <section id=\"view-table\" class=\"hidden\">
      <section id=\"table-section\" class=\"card card--table\">
        <header class=\"card__header\">
          <h3>Detalhamento</h3>
          <div class=\"card__actions\">
            <div class=\"card__search-autocomplete\">
              <input id=\"busca\" class=\"input input--sm\" type=\"search\" placeholder=\"Contrato (Ex.: CT-2025-001234)\" autocomplete=\"off\" aria-autocomplete=\"list\" aria-expanded=\"false\" aria-owns=\"contract-suggest\"/>
              <div id=\"contract-suggest\" class=\"contract-suggest\" role=\"listbox\" aria-label=\"Sugestões de contratos\" hidden></div>
            </div>
          </div>
        </header>
        <div id=\"gridRanking\"></div>
      </section>
    </section>

    <!-- Aqui eu apresento a visão executiva com gráficos e rankings -->
    <section id=\"view-exec\" class=\"hidden view-panel\">
      <section class=\"card card--exec\">
        <header class=\"card__header exec-head\">
          <div class=\"title-subtitle\">
            <h3>Visão executiva</h3>
            <div class=\"muted\" id=\"exec-context\"></div>
          </div>
          <div class=\"exec-head__actions\">
            <button id=\"btn-export-onepage\" class=\"btn btn--ghost btn--sm\" type=\"button\">
              <i class=\"ti ti-file-type-pdf\" aria-hidden=\"true\"></i>
              <span>Exportar PDF</span>
            </button>
          </div>
        </header>

        <div id=\"exec-kpis\" class=\"exec-kpis\"></div>

        <section class=\"exec-panel exec-chart\">
          <div class=\"exec-h exec-h--chart\">
            <span id=\"exec-chart-title\">Evolução mensal por seção</span>
          </div>
          <div id=\"exec-chart\" class=\"chart\" role=\"img\" aria-label=\"Linhas mensais de atingimento por seção\"></div>
          <div id=\"exec-chart-legend\" class=\"chart-legend\"></div>
        </section>

        <div class=\"exec-grid\">
          <div class=\"exec-row exec-row--rank\">
            <div class=\"exec-row__title\" id=\"exec-rank-title\">Desempenho por unidade</div>
            <div class=\"exec-row__grid\">
              <section class=\"exec-panel exec-panel--rank\" id=\"exec-rank-top-panel\">
                <div class=\"exec-h\"><span class=\"exec-panel__title\">Top 5</span></div>
                <div id=\"exec-rank-top\" class=\"rank-mini\"></div>
              </section>
              <section class=\"exec-panel exec-panel--rank\" id=\"exec-rank-bottom-panel\">
                <div class=\"exec-h\"><span class=\"exec-panel__title\">Bottom 5</span></div>
                <div id=\"exec-rank-bottom\" class=\"rank-mini\"></div>
              </section>
            </div>
          </div>

          <div class=\"exec-row exec-row--status\">
            <div class=\"exec-row__title\" id=\"exec-status-title\">Status das unidades</div>
            <div class=\"exec-row__grid exec-row__grid--status\">
              <section class=\"exec-panel exec-panel--status\" data-status=\"hit\">
                <div class=\"exec-h\"><span class=\"status-card__title\">Atingidas</span></div>
                <div id=\"exec-status-hit\" class=\"list-mini\"></div>
              </section>
              <section class=\"exec-panel exec-panel--status\" data-status=\"quase\">
                <div class=\"exec-h\"><span class=\"status-card__title\">Quase lá</span></div>
                <div id=\"exec-status-quase\" class=\"list-mini\"></div>
              </section>
              <section class=\"exec-panel exec-panel--status\" data-status=\"longe\">
                <div class=\"exec-h\"><span class=\"status-card__title\">Longe</span></div>
                <div id=\"exec-status-longe\" class=\"list-mini\"></div>
              </section>
            </div>
          </div>

          <section class=\"exec-panel exec-panel--scroll\">
            <div class=\"exec-h exec-h--heatmap\">
              <span id=\"exec-heatmap-title\">Heatmap</span>
              <div class=\"segmented seg-mini\" id=\"exec-heatmap-toggle\" role=\"tablist\" aria-label=\"Modo do heatmap\">
                <button type=\"button\" class=\"seg-btn is-active\" data-hm=\"secoes\">Seções</button>
                <button type=\"button\" class=\"seg-btn\" data-hm=\"meta\">Meta</button>
              </div>
            </div>
            <div id=\"exec-heatmap\" class=\"hm\"></div>
          </section>
        </div>
      </section>
    </section>

    <!-- Aqui eu deixo a nova aba de simuladores pronta para receber conteúdo -->
    <section id=\"view-simuladores\" class=\"hidden view-panel\">
      <section class=\"card card--simuladores\">
        <header class=\"card__header\">
          <div class=\"title-subtitle\">
            <h3>Simuladores</h3>
            <p class=\"muted\">Monte cenários \"E se?\" e antecipe pontos e remuneração antes de fechar negócios.</p>
          </div>
        </header>
        <div class=\"sim-whatif\" id=\"sim-whatif\" aria-live=\"polite\">
          <div class=\"sim-whatif__intro\">
            <span class=\"sim-whatif__badge\">Beta</span>
            <p>Selecione um indicador com meta em valor ou quantidade, informe o acréscimo esperado e acompanhe o impacto instantâneo.</p>
          </div>
          <form id=\"sim-whatif-form\" class=\"sim-whatif__form\">
            <div class=\"sim-field\">
              <label for=\"sim-whatif-indicador\">Indicador</label>
              <select id=\"sim-whatif-indicador\" class=\"input\" required>
                <option value=\"\" disabled selected>Carregando indicadores…</option>
              </select>
            </div>
            <div class=\"sim-field sim-field--split\">
              <div class=\"sim-field__label\">
                <label for=\"sim-whatif-extra\">Quanto você pretende vender a mais?</label>
                <span id=\"sim-whatif-unit\" class=\"sim-field__hint\"></span>
              </div>
              <div class=\"sim-field__control\">
                <div class=\"sim-input-group\">
                  <span class=\"sim-input-group__prefix\" id=\"sim-whatif-prefix\" aria-hidden=\"true\"></span>
                  <input id=\"sim-whatif-extra\" class=\"input\" type=\"number\" inputmode=\"decimal\" min=\"0\" step=\"1\" value=\"0\" aria-describedby=\"sim-whatif-unit\"/>
                </div>
              </div>
            </div>
            <div class=\"sim-quick\" id=\"sim-whatif-shortcuts\" aria-label=\"Atalhos de simulação\"></div>
          </form>
          <section class=\"sim-whatif__results\" aria-live=\"polite\" aria-atomic=\"true\">
            <header class=\"sim-whatif__results-head\">
              <h4 id=\"sim-whatif-title\">Escolha um indicador para iniciar</h4>
              <p id=\"sim-whatif-subtitle\" class=\"muted\">Os valores consideram os filtros aplicados na visão principal.</p>
            </header>
            <div class=\"sim-whatif__cards\" id=\"sim-whatif-cards\"></div>
            <footer class=\"sim-whatif__foot\" id=\"sim-whatif-foot\"></footer>
          </section>
        </div>
      </section>
    </section>

    <!-- Aqui eu apresento a visão de campanhas e simuladores -->
    <section id=\"view-campanhas\" class=\"hidden view-panel\">
      <section class=\"card card--campanhas\">
        <header class=\"card__header camp-header\">
          <div class=\"title-subtitle\">
            <h3>Campanhas</h3>
            <p class=\"muted\" id=\"camp-cycle\"></p>
          </div>
          <div class=\"camp-header__controls\">
            <label for=\"campanha-sprint\" class=\"muted\">Selecione a campanha</label>
            <select id=\"campanha-sprint\" class=\"input input--sm\"></select>
          </div>
        </header>

        <div class=\"camp-hero\">
          <div class=\"camp-hero__info\">
            <p id=\"camp-note\"></p>
            <div class=\"camp-period camp-period--validity\">
              <i class=\"ti ti-calendar-event\" aria-hidden=\"true\"></i>
              <span id=\"camp-validity\" class=\"camp-validity muted\"></span>
            </div>
          </div>
          <div class=\"camp-hero__stats\" id=\"camp-headline\"></div>
        </div>

        <div class=\"camp-kpi-grid\" id=\"camp-kpis\"></div>
      </section>

      <section class=\"card card--camp-sims\">
        <header class=\"card__header\">
          <div class=\"title-subtitle\">
            <h4>Simuladores da sprint</h4>
            <p class=\"muted\">Projete rapidamente o resultado da equipe ou de um gerente ajustando o atingimento de cada indicador.</p>
          </div>
        </header>
        <div class=\"sim-grid\">
          <article id=\"sim-equipe\" class=\"sim-card\"></article>
          <article id=\"sim-individual\" class=\"sim-card\"></article>
        </div>
        <p class=\"sim-footnote muted\">Cada indicador respeita o intervalo de 0% a 150% para cálculo de pontos. Valores acima disso são desconsiderados.</p>
      </section>

      <section class=\"card card--camp-ranking\">
        <header class=\"card__header\">
          <div class=\"title-subtitle\">
            <h4>Ranking da campanha</h4>
            <p class=\"muted\" id=\"camp-ranking-desc\">Acompanhe a performance das regionais e a elegibilidade frente aos critérios mínimos.</p>
          </div>
        </header>
        <div id=\"camp-ranking\"></div>
      </section>
    </section>
  </main>

{% include 'components/sections/detail-designer.twig' %}
{% include 'components/filters/filters-backdrop.twig' %}
{% include 'components/modals/omega-modal.twig' %}
{% include 'components/modals/leads-modal.twig' %}

{% include 'layouts/footer.twig' %}
{% include 'layouts/scripts.twig' %}
</body>
</html>

", "index.twig", "/home/daniel/Documentos/pobj-slim/src/Presentation/Views/index.twig");
    }
}
