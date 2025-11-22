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

/* layouts/header-topbar.twig */
class __TwigTemplate_5e7e4734f123ff9d66a6b6a96704b668e7729a60a5a845777c8bcc7995d8664c extends \Twig\Template
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
        echo "  <!-- Aqui eu montei a topbar com as informações do usuário logado -->
  <header class=\"topbar\">
    <div class=\"topbar__left\">
      <div class=\"logo\">
        <span class=\"logo__mark\" aria-hidden=\"true\"></span>
        <span class=\"logo__text\">Bradesco</span>
      </div>
    </div>

    <div class=\"topbar__right\">
      <div class=\"topbar__notifications\">
        <button id=\"btn-topbar-notifications\" class=\"topbar__bell\" type=\"button\" aria-haspopup=\"true\" aria-expanded=\"false\" aria-controls=\"topbar-notification-panel\">
          <i class=\"ti ti-bell\" aria-hidden=\"true\"></i>
          <span id=\"topbar-notification-badge\" class=\"topbar__badge\" hidden>0</span>
          <span class=\"sr-only\">Abrir notificações</span>
        </button>
        <div id=\"topbar-notification-panel\" class=\"topbar-notification-panel\" role=\"menu\" aria-hidden=\"true\" hidden>
          <p class=\"topbar-notification-panel__empty\">Nenhuma notificação no momento.</p>
        </div>
      </div>
      <div class=\"userbox\">
        <button class=\"userbox__trigger\" id=\"btn-user-menu\" type=\"button\" aria-haspopup=\"true\" aria-expanded=\"false\">
          <img class=\"userbox__avatar\" src=\"https://i.pravatar.cc/80?img=12\" alt=\"Foto do usuário\"/>
          <span class=\"userbox__name\">X Burguer</span>
          <i class=\"ti ti-chevron-down\" aria-hidden=\"true\"></i>
        </button>
        <div class=\"userbox__menu\" id=\"user-menu\" role=\"menu\" aria-hidden=\"true\" hidden>
          <span class=\"userbox__menu-title\">Links úteis</span>
          <button class=\"userbox__menu-item\" type=\"button\" data-action=\"omega\">Omega</button>
          <button class=\"userbox__menu-item\" type=\"button\" data-action=\"leads\">Leads propensos</button>
          <button class=\"userbox__menu-item\" type=\"button\" data-action=\"portal\">Portal PJ</button>
          <div class=\"userbox__submenu\">
            <button class=\"userbox__menu-item userbox__menu-item--has-sub\" type=\"button\" data-submenu=\"manuais\" aria-expanded=\"false\">
              Manuais
              <i class=\"ti ti-chevron-right\" aria-hidden=\"true\"></i>
            </button>
            <div class=\"userbox__submenu-list\" id=\"user-submenu-manuais\" hidden>
              <button class=\"userbox__menu-item\" type=\"button\" data-action=\"manual1\">Manual 1</button>
              <button class=\"userbox__menu-item\" type=\"button\" data-action=\"manual2\">Manual 2</button>
            </div>
          </div>
          <button class=\"userbox__menu-item\" type=\"button\" data-action=\"mapao\">Mapão de Oportunidades</button>
          <hr class=\"userbox__divider\"/>
          <button class=\"userbox__menu-item userbox__menu-item--logout\" type=\"button\" data-action=\"logout\">
            <i class=\"ti ti-logout-2\" aria-hidden=\"true\"></i>
            <span>Sair</span>
          </button>
        </div>
      </div>
    </div>
  </header>

";
    }

    public function getTemplateName()
    {
        return "layouts/header-topbar.twig";
    }

    public function getDebugInfo()
    {
        return array (  37 => 1,);
    }

    public function getSourceContext()
    {
        return new Source("  <!-- Aqui eu montei a topbar com as informações do usuário logado -->
  <header class=\"topbar\">
    <div class=\"topbar__left\">
      <div class=\"logo\">
        <span class=\"logo__mark\" aria-hidden=\"true\"></span>
        <span class=\"logo__text\">Bradesco</span>
      </div>
    </div>

    <div class=\"topbar__right\">
      <div class=\"topbar__notifications\">
        <button id=\"btn-topbar-notifications\" class=\"topbar__bell\" type=\"button\" aria-haspopup=\"true\" aria-expanded=\"false\" aria-controls=\"topbar-notification-panel\">
          <i class=\"ti ti-bell\" aria-hidden=\"true\"></i>
          <span id=\"topbar-notification-badge\" class=\"topbar__badge\" hidden>0</span>
          <span class=\"sr-only\">Abrir notificações</span>
        </button>
        <div id=\"topbar-notification-panel\" class=\"topbar-notification-panel\" role=\"menu\" aria-hidden=\"true\" hidden>
          <p class=\"topbar-notification-panel__empty\">Nenhuma notificação no momento.</p>
        </div>
      </div>
      <div class=\"userbox\">
        <button class=\"userbox__trigger\" id=\"btn-user-menu\" type=\"button\" aria-haspopup=\"true\" aria-expanded=\"false\">
          <img class=\"userbox__avatar\" src=\"https://i.pravatar.cc/80?img=12\" alt=\"Foto do usuário\"/>
          <span class=\"userbox__name\">X Burguer</span>
          <i class=\"ti ti-chevron-down\" aria-hidden=\"true\"></i>
        </button>
        <div class=\"userbox__menu\" id=\"user-menu\" role=\"menu\" aria-hidden=\"true\" hidden>
          <span class=\"userbox__menu-title\">Links úteis</span>
          <button class=\"userbox__menu-item\" type=\"button\" data-action=\"omega\">Omega</button>
          <button class=\"userbox__menu-item\" type=\"button\" data-action=\"leads\">Leads propensos</button>
          <button class=\"userbox__menu-item\" type=\"button\" data-action=\"portal\">Portal PJ</button>
          <div class=\"userbox__submenu\">
            <button class=\"userbox__menu-item userbox__menu-item--has-sub\" type=\"button\" data-submenu=\"manuais\" aria-expanded=\"false\">
              Manuais
              <i class=\"ti ti-chevron-right\" aria-hidden=\"true\"></i>
            </button>
            <div class=\"userbox__submenu-list\" id=\"user-submenu-manuais\" hidden>
              <button class=\"userbox__menu-item\" type=\"button\" data-action=\"manual1\">Manual 1</button>
              <button class=\"userbox__menu-item\" type=\"button\" data-action=\"manual2\">Manual 2</button>
            </div>
          </div>
          <button class=\"userbox__menu-item\" type=\"button\" data-action=\"mapao\">Mapão de Oportunidades</button>
          <hr class=\"userbox__divider\"/>
          <button class=\"userbox__menu-item userbox__menu-item--logout\" type=\"button\" data-action=\"logout\">
            <i class=\"ti ti-logout-2\" aria-hidden=\"true\"></i>
            <span>Sair</span>
          </button>
        </div>
      </div>
    </div>
  </header>

", "layouts/header-topbar.twig", "/home/daniel/Documentos/pobj-slim/src/Presentation/Views/layouts/header-topbar.twig");
    }
}
