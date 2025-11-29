-- MySQL dump 10.13  Distrib 5.7.44, for Linux (x86_64)
--
-- Host: localhost    Database: pobj_refactor
-- ------------------------------------------------------
-- Server version	5.7.44

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `agencias`
--

DROP TABLE IF EXISTS `agencias`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `agencias` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `regional_id` int(10) unsigned NOT NULL,
  `nome` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `porte` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_agencia_regional_nome` (`regional_id`,`nome`),
  CONSTRAINT `fk_agencias_regional` FOREIGN KEY (`regional_id`) REFERENCES `regionais` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1268 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `cargos`
--

DROP TABLE IF EXISTS `cargos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cargos` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `nome` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nome` (`nome`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `d_calendario`
--

DROP TABLE IF EXISTS `d_calendario`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `d_calendario` (
  `data` date NOT NULL,
  `ano` int(11) NOT NULL,
  `mes` tinyint(4) NOT NULL,
  `mes_nome` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `dia` tinyint(4) NOT NULL,
  `dia_da_semana` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `semana` tinyint(4) NOT NULL,
  `trimestre` tinyint(4) NOT NULL,
  `semestre` tinyint(4) NOT NULL,
  `eh_dia_util` tinyint(1) NOT NULL,
  PRIMARY KEY (`data`),
  KEY `idx_d_calendario_mes` (`ano`,`mes`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `d_estrutura`
--

DROP TABLE IF EXISTS `d_estrutura`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `d_estrutura` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `funcional` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nome` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `cargo_id` int(10) unsigned NOT NULL,
  `segmento_id` int(10) unsigned DEFAULT NULL,
  `diretoria_id` int(10) unsigned DEFAULT NULL,
  `regional_id` int(10) unsigned DEFAULT NULL,
  `agencia_id` int(10) unsigned DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `funcional` (`funcional`),
  KEY `fk_estrutura_cargo` (`cargo_id`),
  KEY `fk_estrutura_segmento` (`segmento_id`),
  KEY `fk_estrutura_diretoria` (`diretoria_id`),
  KEY `fk_estrutura_regional` (`regional_id`),
  KEY `fk_estrutura_agencia` (`agencia_id`),
  CONSTRAINT `fk_estrutura_agencia` FOREIGN KEY (`agencia_id`) REFERENCES `agencias` (`id`),
  CONSTRAINT `fk_estrutura_cargo` FOREIGN KEY (`cargo_id`) REFERENCES `cargos` (`id`),
  CONSTRAINT `fk_estrutura_diretoria` FOREIGN KEY (`diretoria_id`) REFERENCES `diretorias` (`id`),
  CONSTRAINT `fk_estrutura_regional` FOREIGN KEY (`regional_id`) REFERENCES `regionais` (`id`),
  CONSTRAINT `fk_estrutura_segmento` FOREIGN KEY (`segmento_id`) REFERENCES `segmentos` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `d_produtos`
--

DROP TABLE IF EXISTS `d_produtos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `d_produtos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `familia_id` int(11) NOT NULL,
  `indicador_id` int(11) NOT NULL,
  `subindicador_id` int(11) DEFAULT NULL,
  `peso` decimal(10,2) NOT NULL DEFAULT '0.00',
  `metrica` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'valor',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_indicador_sub` (`indicador_id`,`subindicador_id`),
  KEY `idx_familia` (`familia_id`),
  KEY `idx_indicador` (`indicador_id`),
  KEY `idx_subindicador` (`subindicador_id`),
  CONSTRAINT `d_produtos_ibfk_1` FOREIGN KEY (`familia_id`) REFERENCES `familia` (`id`),
  CONSTRAINT `d_produtos_ibfk_2` FOREIGN KEY (`indicador_id`) REFERENCES `indicador` (`id`),
  CONSTRAINT `d_produtos_ibfk_3` FOREIGN KEY (`subindicador_id`) REFERENCES `subindicador` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=84 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `d_status_indicadores`
--

DROP TABLE IF EXISTS `d_status_indicadores`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `d_status_indicadores` (
  `id` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_d_status_nome` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `diretorias`
--

DROP TABLE IF EXISTS `diretorias`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `diretorias` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `segmento_id` int(10) unsigned NOT NULL,
  `nome` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_diretoria_segmento_nome` (`segmento_id`,`nome`),
  CONSTRAINT `fk_diretorias_segmento` FOREIGN KEY (`segmento_id`) REFERENCES `segmentos` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8608 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `f_detalhes`
--

DROP TABLE IF EXISTS `f_detalhes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `f_detalhes` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `contrato_id` varchar(80) COLLATE utf8mb4_unicode_ci NOT NULL,
  `registro_id` varchar(60) COLLATE utf8mb4_unicode_ci NOT NULL,
  `funcional` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `id_produto` int(11) NOT NULL,
  `dt_cadastro` date NOT NULL,
  `competencia` date NOT NULL,
  `valor_meta` decimal(18,2) DEFAULT NULL,
  `valor_realizado` decimal(18,2) DEFAULT NULL,
  `quantidade` decimal(18,4) DEFAULT NULL,
  `peso` decimal(18,4) DEFAULT NULL,
  `pontos` decimal(18,4) DEFAULT NULL,
  `dt_vencimento` date DEFAULT NULL,
  `dt_cancelamento` date DEFAULT NULL,
  `motivo_cancelamento` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `canal_venda` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tipo_venda` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `condicao_pagamento` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status_id` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_fd_contrato` (`contrato_id`),
  KEY `idx_fd_funcional` (`funcional`),
  KEY `idx_fd_registro` (`registro_id`),
  KEY `idx_fd_produto` (`id_produto`),
  KEY `idx_fd_dt_cadastro` (`dt_cadastro`),
  KEY `idx_fd_competencia` (`competencia`),
  KEY `fk_fd_status` (`status_id`),
  CONSTRAINT `fk_fd_comp` FOREIGN KEY (`competencia`) REFERENCES `d_calendario` (`data`) ON UPDATE CASCADE,
  CONSTRAINT `fk_fd_dt_cadastro` FOREIGN KEY (`dt_cadastro`) REFERENCES `d_calendario` (`data`) ON UPDATE CASCADE,
  CONSTRAINT `fk_fd_estrutura` FOREIGN KEY (`funcional`) REFERENCES `d_estrutura` (`funcional`) ON UPDATE CASCADE,
  CONSTRAINT `fk_fd_produto` FOREIGN KEY (`id_produto`) REFERENCES `d_produtos` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_fd_status` FOREIGN KEY (`status_id`) REFERENCES `d_status_indicadores` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `f_historico_ranking_pobj`
--

DROP TABLE IF EXISTS `f_historico_ranking_pobj`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `f_historico_ranking_pobj` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `data` date NOT NULL,
  `funcional` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `grupo` int(11) DEFAULT NULL,
  `ranking` int(11) DEFAULT NULL,
  `realizado` decimal(18,2) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_hist_data` (`data`),
  KEY `idx_hist_funcional` (`funcional`),
  KEY `idx_hist_ranking` (`ranking`),
  CONSTRAINT `fk_hist_pobj_calendario` FOREIGN KEY (`data`) REFERENCES `d_calendario` (`data`) ON UPDATE CASCADE,
  CONSTRAINT `fk_hist_pobj_estrutura` FOREIGN KEY (`funcional`) REFERENCES `d_estrutura` (`funcional`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `f_meta`
--

DROP TABLE IF EXISTS `f_meta`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `f_meta` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `data_meta` date NOT NULL,
  `funcional` varchar(16) COLLATE utf8mb4_unicode_ci NOT NULL,
  `produto_id` int(11) NOT NULL,
  `meta_mensal` decimal(18,2) NOT NULL DEFAULT '0.00',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `ix_meta_data` (`data_meta`),
  KEY `ix_meta_func_data` (`funcional`,`data_meta`),
  KEY `fk_f_meta__produto` (`produto_id`),
  CONSTRAINT `fk_f_meta__d_estrutura` FOREIGN KEY (`funcional`) REFERENCES `d_estrutura` (`funcional`),
  CONSTRAINT `fk_f_meta__produto` FOREIGN KEY (`produto_id`) REFERENCES `d_produtos` (`id`),
  CONSTRAINT `fk_fm_cal` FOREIGN KEY (`data_meta`) REFERENCES `d_calendario` (`data`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `f_pontos`
--

DROP TABLE IF EXISTS `f_pontos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `f_pontos` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `funcional` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `produto_id` int(11) NOT NULL,
  `meta` decimal(18,2) NOT NULL DEFAULT '0.00',
  `realizado` decimal(18,2) NOT NULL DEFAULT '0.00',
  `data_realizado` date DEFAULT NULL,
  `dt_atualizacao` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_fp_funcional` (`funcional`),
  KEY `idx_fp_produto` (`produto_id`),
  KEY `idx_fp_data_realizado` (`data_realizado`),
  CONSTRAINT `fk_fpontos_calendario` FOREIGN KEY (`data_realizado`) REFERENCES `d_calendario` (`data`) ON UPDATE CASCADE,
  CONSTRAINT `fk_fpontos_estrutura` FOREIGN KEY (`funcional`) REFERENCES `d_estrutura` (`funcional`) ON UPDATE CASCADE,
  CONSTRAINT `fk_fpontos_produto` FOREIGN KEY (`produto_id`) REFERENCES `d_produtos` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `f_realizados`
--

DROP TABLE IF EXISTS `f_realizados`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `f_realizados` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `id_contrato` char(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `funcional` varchar(16) COLLATE utf8mb4_unicode_ci NOT NULL,
  `data_realizado` date NOT NULL,
  `realizado` decimal(18,2) NOT NULL DEFAULT '0.00',
  `produto_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_fr_data` (`data_realizado`),
  KEY `idx_fr_func_data` (`funcional`,`data_realizado`),
  KEY `idx_fr_contrato` (`id_contrato`),
  KEY `idx_fr_produto` (`produto_id`),
  CONSTRAINT `fk_fr_calendario` FOREIGN KEY (`data_realizado`) REFERENCES `d_calendario` (`data`) ON UPDATE CASCADE,
  CONSTRAINT `fk_fr_estrutura` FOREIGN KEY (`funcional`) REFERENCES `d_estrutura` (`funcional`) ON UPDATE CASCADE,
  CONSTRAINT `fk_fr_produto` FOREIGN KEY (`produto_id`) REFERENCES `d_produtos` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=27 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `f_variavel`
--

DROP TABLE IF EXISTS `f_variavel`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `f_variavel` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `funcional` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `meta` decimal(18,2) NOT NULL DEFAULT '0.00',
  `variavel` decimal(18,2) NOT NULL DEFAULT '0.00',
  `dt_atualizacao` date NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_fv_funcional` (`funcional`),
  KEY `idx_fv_dt` (`dt_atualizacao`),
  CONSTRAINT `fk_f_variavel_calendario` FOREIGN KEY (`dt_atualizacao`) REFERENCES `d_calendario` (`data`) ON UPDATE CASCADE,
  CONSTRAINT `fk_f_variavel_estrutura` FOREIGN KEY (`funcional`) REFERENCES `d_estrutura` (`funcional`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `familia`
--

DROP TABLE IF EXISTS `familia`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `familia` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nm_familia` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_familia` (`nm_familia`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `indicador`
--

DROP TABLE IF EXISTS `indicador`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `indicador` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nm_indicador` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_indicador` (`nm_indicador`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `regionais`
--

DROP TABLE IF EXISTS `regionais`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `regionais` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `diretoria_id` int(10) unsigned NOT NULL,
  `nome` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_regional_diretoria_nome` (`diretoria_id`,`nome`),
  CONSTRAINT `fk_regionais_diretoria` FOREIGN KEY (`diretoria_id`) REFERENCES `diretorias` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8487 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `segmentos`
--

DROP TABLE IF EXISTS `segmentos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `segmentos` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `nome` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nome` (`nome`)
) ENGINE=InnoDB AUTO_INCREMENT=8608 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `subindicador`
--

DROP TABLE IF EXISTS `subindicador`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `subindicador` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `indicador_id` int(11) NOT NULL,
  `nm_subindicador` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_indicador_sub` (`indicador_id`,`nm_subindicador`),
  CONSTRAINT `subindicador_ibfk_1` FOREIGN KEY (`indicador_id`) REFERENCES `indicador` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-11-25  3:21:54
