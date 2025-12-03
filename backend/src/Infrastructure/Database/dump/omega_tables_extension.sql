-- Tabela para armazenar relacionamento supervisor-analista
CREATE TABLE IF NOT EXISTS `omega_team_analysts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `supervisor_id` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `analyst_id` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_supervisor_analyst` (`supervisor_id`, `analyst_id`),
  KEY `idx_supervisor` (`supervisor_id`),
  KEY `idx_analyst` (`analyst_id`),
  CONSTRAINT `fk_team_analysts_supervisor` FOREIGN KEY (`supervisor_id`) REFERENCES `omega_usuarios` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_team_analysts_analyst` FOREIGN KEY (`analyst_id`) REFERENCES `omega_usuarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Tabela para armazenar notificações do Omega
CREATE TABLE IF NOT EXISTS `omega_notifications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ticket_id` varchar(60) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ticket_subject` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `date` datetime DEFAULT NULL,
  `actor_id` varchar(40) COLLATE utf8_unicode_ci DEFAULT NULL,
  `action` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `comment` text COLLATE utf8_unicode_ci,
  `status` varchar(40) COLLATE utf8_unicode_ci DEFAULT NULL,
  `type` varchar(50) COLLATE utf8_unicode_ci DEFAULT 'status_update',
  `read` tinyint(1) DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_ticket` (`ticket_id`),
  KEY `idx_actor` (`actor_id`),
  KEY `idx_read` (`read`),
  KEY `idx_created` (`created_at`),
  CONSTRAINT `fk_omega_notifications_ticket` FOREIGN KEY (`ticket_id`) REFERENCES `omega_chamados` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_omega_notifications_actor` FOREIGN KEY (`actor_id`) REFERENCES `omega_usuarios` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Tabela para armazenar notificações do POBJ
CREATE TABLE IF NOT EXISTS `pobj_notifications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ticket_id` varchar(60) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ticket_subject` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `date` datetime DEFAULT NULL,
  `actor_id` varchar(40) COLLATE utf8_unicode_ci DEFAULT NULL,
  `action` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `comment` text COLLATE utf8_unicode_ci,
  `status` varchar(40) COLLATE utf8_unicode_ci DEFAULT NULL,
  `read` tinyint(1) DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_ticket` (`ticket_id`),
  KEY `idx_actor` (`actor_id`),
  KEY `idx_read` (`read`),
  KEY `idx_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;



