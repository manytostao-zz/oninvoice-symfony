# SQL Manager 2010 for MySQL 4.5.0.9
# ---------------------------------------
# Host     : localhost
# Port     : 3306
# Database : fusionclone


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES latin1 */;

SET FOREIGN_KEY_CHECKS=0;

DROP TABLE IF EXISTS `appparam`;

CREATE TABLE `appparam` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `valor` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

#
# Structure for the `nommone` table : 
#

DROP TABLE IF EXISTS `nommone`;

CREATE TABLE `nommone` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `codigo` varchar(5) COLLATE utf8_unicode_ci NOT NULL,
  `descripcion` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `simbolo` varchar(5) COLLATE utf8_unicode_ci NOT NULL,
  `ubicaSimbol` tinyint(1) NOT NULL,
  `signDecimal` varchar(1) COLLATE utf8_unicode_ci NOT NULL,
  `signMillares` varchar(1) COLLATE utf8_unicode_ci NOT NULL,
  `tasa` decimal(10,7) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

#
# Structure for the `clientes` table : 
#

DROP TABLE IF EXISTS `clientes`;

CREATE TABLE `clientes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `direccion` longtext COLLATE utf8_unicode_ci,
  `telefono` decimal(10,0) DEFAULT NULL,
  `fax` decimal(10,0) DEFAULT NULL,
  `movil` decimal(10,0) DEFAULT NULL,
  `email` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `webpage` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `activo` tinyint(1) DEFAULT NULL,
  `defMone_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_50FE07D75472DAA2` (`defMone_id`),
  CONSTRAINT `FK_50FE07D75472DAA2` FOREIGN KEY (`defMone_id`) REFERENCES `nommone` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

#
# Structure for the `nomtdoc` table : 
#

DROP TABLE IF EXISTS `nomtdoc`;

CREATE TABLE `nomtdoc` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `codigo` varchar(3) COLLATE utf8_unicode_ci NOT NULL,
  `descripcion` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

#
# Structure for the `nomesta` table : 
#

DROP TABLE IF EXISTS `nomesta`;

CREATE TABLE `nomesta` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tdoc_id` int(11) DEFAULT NULL,
  `descripcion` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_557E758FBDDFC259` (`tdoc_id`),
  CONSTRAINT `FK_557E758FBDDFC259` FOREIGN KEY (`tdoc_id`) REFERENCES `nomtdoc` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

#
# Structure for the `nomtdocconf` table : 
#

DROP TABLE IF EXISTS `nomtdocconf`;

CREATE TABLE `nomtdocconf` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tdoc_id` int(11) DEFAULT NULL,
  `descripcion` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `consecutivo` int(11) NOT NULL,
  `prefijo` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `cantDigCons` int(11) NOT NULL,
  `anno` tinyint(1) NOT NULL,
  `mes` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_F8C5E98EBDDFC259` (`tdoc_id`),
  CONSTRAINT `FK_F8C5E98EBDDFC259` FOREIGN KEY (`tdoc_id`) REFERENCES `nomtdoc` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

#
# Structure for the `cotizaciones` table : 
#

DROP TABLE IF EXISTS `cotizaciones`;

CREATE TABLE `cotizaciones` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `moneda_id` int(11) DEFAULT NULL,
  `estado_id` int(11) DEFAULT NULL,
  `cliente_id` int(11) DEFAULT NULL,
  `codigo` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `fecha` date DEFAULT NULL,
  `fechaVenc` date DEFAULT NULL,
  `tasa` decimal(10,7) DEFAULT NULL,
  `importe` decimal(10,2) DEFAULT NULL,
  `terms` longtext COLLATE utf8_unicode_ci,
  `pie` longtext COLLATE utf8_unicode_ci,
  `tdocConf_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_12CE14AEB77634D2` (`moneda_id`),
  KEY `IDX_12CE14AE9F5A440B` (`estado_id`),
  KEY `IDX_12CE14AEDE734E51` (`cliente_id`),
  KEY `IDX_12CE14AEDC07200` (`tdocConf_id`),
  CONSTRAINT `FK_12CE14AEDC07200` FOREIGN KEY (`tdocConf_id`) REFERENCES `nomtdocconf` (`id`),
  CONSTRAINT `FK_12CE14AE9F5A440B` FOREIGN KEY (`estado_id`) REFERENCES `nomesta` (`id`),
  CONSTRAINT `FK_12CE14AEB77634D2` FOREIGN KEY (`moneda_id`) REFERENCES `nommone` (`id`),
  CONSTRAINT `FK_12CE14AEDE734E51` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

#
# Structure for the `nomimp` table : 
#

DROP TABLE IF EXISTS `nomimp`;

CREATE TABLE `nomimp` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `porcentaje` decimal(10,2) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

#
# Structure for the `cotimp` table : 
#

DROP TABLE IF EXISTS `cotimp`;

CREATE TABLE `cotimp` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cotizacion_id` int(11) DEFAULT NULL,
  `impuesto_id` int(11) DEFAULT NULL,
  `antesImpItem` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_3F80D999307090AA` (`cotizacion_id`),
  KEY `IDX_3F80D999D23B6BE5` (`impuesto_id`),
  CONSTRAINT `FK_3F80D999D23B6BE5` FOREIGN KEY (`impuesto_id`) REFERENCES `nomimp` (`id`),
  CONSTRAINT `FK_3F80D999307090AA` FOREIGN KEY (`cotizacion_id`) REFERENCES `cotizaciones` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

#
# Structure for the `nomprod` table : 
#

DROP TABLE IF EXISTS `nomprod`;

CREATE TABLE `nomprod` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `descripcion` longtext COLLATE utf8_unicode_ci NOT NULL,
  `precio` decimal(10,0) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

#
# Structure for the `cotitem` table : 
#

DROP TABLE IF EXISTS `cotitem`;

CREATE TABLE `cotitem` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cotizacion_id` int(11) DEFAULT NULL,
  `producto_id` int(11) DEFAULT NULL,
  `impuesto_id` int(11) DEFAULT NULL,
  `nombre` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `descripcion` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `precio` decimal(10,2) NOT NULL,
  `cantidad` decimal(10,2) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_4CAF27BA307090AA` (`cotizacion_id`),
  KEY `IDX_4CAF27BA7645698E` (`producto_id`),
  KEY `IDX_4CAF27BAD23B6BE5` (`impuesto_id`),
  CONSTRAINT `FK_4CAF27BAD23B6BE5` FOREIGN KEY (`impuesto_id`) REFERENCES `nomimp` (`id`),
  CONSTRAINT `FK_4CAF27BA307090AA` FOREIGN KEY (`cotizacion_id`) REFERENCES `cotizaciones` (`id`),
  CONSTRAINT `FK_4CAF27BA7645698E` FOREIGN KEY (`producto_id`) REFERENCES `nomprod` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

#
# Structure for the `facturas` table : 
#

DROP TABLE IF EXISTS `facturas`;

CREATE TABLE `facturas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `moneda_id` int(11) DEFAULT NULL,
  `estado_id` int(11) DEFAULT NULL,
  `cliente_id` int(11) DEFAULT NULL,
  `codigo` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `fecha` date DEFAULT NULL,
  `fechaVenc` date DEFAULT NULL,
  `tasa` decimal(10,7) DEFAULT NULL,
  `importe` decimal(10,2) DEFAULT NULL,
  `saldo` decimal(10,2) DEFAULT NULL,
  `terms` longtext COLLATE utf8_unicode_ci,
  `pie` longtext COLLATE utf8_unicode_ci,
  `tdocConf_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_622B9C0FB77634D2` (`moneda_id`),
  KEY `IDX_622B9C0F9F5A440B` (`estado_id`),
  KEY `IDX_622B9C0FDE734E51` (`cliente_id`),
  KEY `IDX_622B9C0FDC07200` (`tdocConf_id`),
  CONSTRAINT `FK_622B9C0FDC07200` FOREIGN KEY (`tdocConf_id`) REFERENCES `nomtdocconf` (`id`),
  CONSTRAINT `FK_622B9C0F9F5A440B` FOREIGN KEY (`estado_id`) REFERENCES `nomesta` (`id`),
  CONSTRAINT `FK_622B9C0FB77634D2` FOREIGN KEY (`moneda_id`) REFERENCES `nommone` (`id`),
  CONSTRAINT `FK_622B9C0FDE734E51` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=44 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

#
# Structure for the `facturaimp` table : 
#

DROP TABLE IF EXISTS `facturaimp`;

CREATE TABLE `facturaimp` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `factura_id` int(11) DEFAULT NULL,
  `impuesto_id` int(11) DEFAULT NULL,
  `antesImpItem` tinyint(1) NOT NULL,
  `total` decimal(10,3) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_9652B7E4F04F795F` (`factura_id`),
  KEY `IDX_9652B7E4D23B6BE5` (`impuesto_id`),
  CONSTRAINT `FK_9652B7E4D23B6BE5` FOREIGN KEY (`impuesto_id`) REFERENCES `nomimp` (`id`),
  CONSTRAINT `FK_9652B7E4F04F795F` FOREIGN KEY (`factura_id`) REFERENCES `facturas` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

#
# Structure for the `facturaitem` table : 
#

DROP TABLE IF EXISTS `facturaitem`;

CREATE TABLE `facturaitem` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `factura_id` int(11) DEFAULT NULL,
  `producto_id` int(11) DEFAULT NULL,
  `impuesto_id` int(11) DEFAULT NULL,
  `nombre` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `descripcion` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `precio` decimal(10,2) NOT NULL,
  `cantidad` decimal(10,2) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_62B2F855F04F795F` (`factura_id`),
  KEY `IDX_62B2F8557645698E` (`producto_id`),
  KEY `IDX_62B2F855D23B6BE5` (`impuesto_id`),
  CONSTRAINT `FK_62B2F855D23B6BE5` FOREIGN KEY (`impuesto_id`) REFERENCES `nomimp` (`id`),
  CONSTRAINT `FK_62B2F8557645698E` FOREIGN KEY (`producto_id`) REFERENCES `nomprod` (`id`),
  CONSTRAINT `FK_62B2F855F04F795F` FOREIGN KEY (`factura_id`) REFERENCES `facturas` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=45 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

#
# Structure for the `nompagos` table : 
#

DROP TABLE IF EXISTS `nompagos`;

CREATE TABLE `nompagos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

#
# Structure for the `pagos` table : 
#

DROP TABLE IF EXISTS `pagos`;

CREATE TABLE `pagos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `factura_id` int(11) DEFAULT NULL,
  `metodo_id` int(11) DEFAULT NULL,
  `fecha` date NOT NULL,
  `nota` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `importe` decimal(10,2) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_DA9B0DFFF04F795F` (`factura_id`),
  KEY `IDX_DA9B0DFFA45CBFCF` (`metodo_id`),
  CONSTRAINT `FK_DA9B0DFFA45CBFCF` FOREIGN KEY (`metodo_id`) REFERENCES `nompagos` (`id`),
  CONSTRAINT `FK_DA9B0DFFF04F795F` FOREIGN KEY (`factura_id`) REFERENCES `facturas` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

#
# Structure for the `usuario` table : 
#

DROP TABLE IF EXISTS `usuario`;

CREATE TABLE `usuario` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `salt` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `fechaAlta` date NOT NULL,
  `compannia` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `direccion` longtext COLLATE utf8_unicode_ci,
  `telefono` decimal(10,0) DEFAULT NULL,
  `fax` decimal(10,0) DEFAULT NULL,
  `movil` decimal(10,0) DEFAULT NULL,
  `webpage` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `cliente_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

#
# Definition for the `FactStatusChange` Event : 
#

DROP EVENT IF EXISTS `FactStatusChange`;

CREATE EVENT `FactStatusChange`
  ON SCHEDULE EVERY 1 DAY STARTS '2014-09-27 10:25:21'
  ON COMPLETION PRESERVE
  ENABLE
  COMMENT ''  DO
UPDATE `facturas` 
SET `facturas`.`estado_id` = 
	(SELECT `nomesta`.`id` 
    FROM `nomesta`
    WHERE `nomesta`.`descripcion` = 'Vencido' 
      AND `nomesta`.`tdoc_id` = 
      	(SELECT `nomtdoc`.id 
        FROM `nomtdoc`
        WHERE `nomtdoc`.`codigo` = 'FAC'))
WHERE `facturas`.`fechaVenc` <= CURDATE()
AND `facturas`.`estado_id` NOT IN
	(SELECT `nomesta`.`id` 
    FROM `nomesta`
    WHERE `nomesta`.`descripcion` = 'Cancelado' 
    OR `nomesta`.`descripcion` = 'Pagado'
    OR `nomesta`.`descripcion` = 'Borrador'
      AND `nomesta`.`tdoc_id` = 
      	(SELECT `nomtdoc`.id 
        FROM `nomtdoc`
        WHERE `nomtdoc`.`codigo` = 'FAC'));

#
# Data for the `appparam` table  (LIMIT 0,500)
#

INSERT INTO `appparam` (`id`, `nombre`, `valor`) VALUES 
  (1,'MONEDA_BASE','USD');
COMMIT;

#
# Data for the `nommone` table  (LIMIT 0,500)
#

INSERT INTO `nommone` (`id`, `codigo`, `descripcion`, `simbolo`, `ubicaSimbol`, `signDecimal`, `signMillares`, `tasa`) VALUES 
  (1,'USD','Dólar Americano','$',1,',','.',1.0000000),
  (2,'PEN','Sol Peruano','S/.',1,'.',',',2.7800000);
COMMIT;

#
# Data for the `clientes` table  (LIMIT 0,500)
#

INSERT INTO `clientes` (`id`, `nombre`, `direccion`, `telefono`, `fax`, `movil`, `email`, `webpage`, `activo`, `defMone_id`) VALUES 
  (1,'Zutanejo de Tal','Wacanda, entre Palmares y Bohio.',3423423,12234234,2343442,'wacanda@gmail.com','asdasd.www.com',1,2),
  (2,'Fulana De Tal','asdasd',232223,234234,234,'asd@asd.asd','www.ss.com',1,1),
  (3,'Mengano de Tal','Hfhdhcui',NULL,NULL,NULL,'asd@asd.com','gdg.hdy.com',1,2);
COMMIT;

#
# Data for the `nomimp` table  (LIMIT 0,500)
#

INSERT INTO `nomimp` (`id`, `nombre`, `porcentaje`) VALUES 
  (1,'Arancel por transportación',5.00);
COMMIT;

#
# Data for the `nomtdoc` table  (LIMIT 0,500)
#

INSERT INTO `nomtdoc` (`id`, `codigo`, `descripcion`) VALUES 
  (1,'FAC','Factura'),
  (2,'COT','Cotización');
COMMIT;

#
# Data for the `nomtdocconf` table  (LIMIT 0,500)
#

INSERT INTO `nomtdocconf` (`id`, `tdoc_id`, `descripcion`, `consecutivo`, `prefijo`, `cantDigCons`, `anno`, `mes`) VALUES 
  (1,1,'Configuración primaria',51,'FAC#',4,1,1),
  (2,2,'Configuración secundaria',9,'COT-',2,1,1);
COMMIT;

#
# Data for the `nomesta` table  (LIMIT 0,500)
#

INSERT INTO `nomesta` (`id`, `tdoc_id`, `descripcion`) VALUES 
  (1,1,'Borrador'),
  (2,1,'Enviado'),
  (3,1,'Cancelado'),
  (4,1,'Vencido'),
  (5,1,'Pagado'),
  (6,2,'Borrador'),
  (7,2,'Enviado'),
  (8,2,'Cancelado'),
  (9,2,'Aprobado'),
  (10,2,'Rechazado');
COMMIT;

#
# Data for the `cotizaciones` table  (LIMIT 0,500)
#

INSERT INTO `cotizaciones` (`id`, `moneda_id`, `estado_id`, `cliente_id`, `codigo`, `fecha`, `fechaVenc`, `tasa`, `importe`, `terms`, `pie`, `tdocConf_id`) VALUES 
  (1,2,8,1,'COT-20141001','2014-10-03','2014-10-04',1.2154000,0.00,NULL,NULL,2),
  (3,2,9,1,'COT-20141003','2014-10-03','2014-10-04',1.2154000,0.00,NULL,NULL,2),
  (6,2,9,1,'COT-20141006','2014-10-03','2014-10-04',1.2154000,0.00,NULL,NULL,2),
  (7,1,9,2,'COT-20141007','2014-10-03','2014-10-04',0.0000000,257.99,NULL,NULL,2),
  (8,2,6,3,'COT-20141008','2014-10-03','2014-10-04',1.5888880,0.00,NULL,NULL,2);
COMMIT;

#
# Data for the `cotimp` table  (LIMIT 0,500)
#

INSERT INTO `cotimp` (`id`, `cotizacion_id`, `impuesto_id`, `antesImpItem`) VALUES 
  (1,7,1,0);
COMMIT;

#
# Data for the `nomprod` table  (LIMIT 0,500)
#

INSERT INTO `nomprod` (`id`, `nombre`, `descripcion`, `precio`) VALUES 
  (1,'Servicio de instalación','asdasd',12),
  (2,'Papas','asd',12),
  (3,'Producto 1','df',4),
  (4,'Producto 2','1212',12),
  (5,'Producto 3','sdfsdf',21),
  (6,'Arroz','asda',12),
  (7,'Perritos','asdad',15),
  (8,'Papas rellenas','hj',6);
COMMIT;

#
# Data for the `cotitem` table  (LIMIT 0,500)
#

INSERT INTO `cotitem` (`id`, `cotizacion_id`, `producto_id`, `impuesto_id`, `nombre`, `descripcion`, `precio`, `cantidad`) VALUES 
  (1,7,1,1,'Servicio de Instalación','asdasd',234.00,1.00);
COMMIT;

#
# Data for the `facturas` table  (LIMIT 0,500)
#

INSERT INTO `facturas` (`id`, `moneda_id`, `estado_id`, `cliente_id`, `codigo`, `fecha`, `fechaVenc`, `tasa`, `importe`, `saldo`, `terms`, `pie`, `tdocConf_id`) VALUES 
  (14,2,3,1,'FAC#201490014','2014-09-23','2014-09-24',1.0000000,403.20,0.00,NULL,NULL,1),
  (17,2,4,1,'FAC#201490017','2014-09-23','2014-09-24',1.0000000,0.00,0.00,'g','h',1),
  (18,2,4,1,'FAC#201490018','2014-09-23','2014-09-24',1.0000000,0.00,0.00,NULL,NULL,1),
  (19,2,4,1,'FAC#201490019','2014-09-23','2014-09-24',1.0000000,0.00,0.00,NULL,NULL,1),
  (22,2,4,1,'FAC#201490022','2014-09-23','2014-09-24',1.0000000,0.00,0.00,NULL,NULL,1),
  (23,2,4,1,'FAC#201490023','2014-09-23','2014-09-24',1.0000000,0.00,0.00,NULL,NULL,1),
  (25,2,4,1,'FAC#2014100032','2014-09-30','2014-10-01',1.0000000,352.80,337.80,NULL,NULL,1),
  (26,2,4,1,'FAC#2014100033','2014-09-30','2014-10-01',1.0000000,283.50,283.50,NULL,NULL,1),
  (43,2,5,2,'FAC#2014100050','2014-10-05','2014-10-06',1.0000000,39.60,0.00,NULL,NULL,1);
COMMIT;

#
# Data for the `facturaimp` table  (LIMIT 0,500)
#

INSERT INTO `facturaimp` (`id`, `factura_id`, `impuesto_id`, `antesImpItem`, `total`) VALUES 
  (14,43,1,1,1.800);
COMMIT;

#
# Data for the `facturaitem` table  (LIMIT 0,500)
#

INSERT INTO `facturaitem` (`id`, `factura_id`, `producto_id`, `impuesto_id`, `nombre`, `descripcion`, `precio`, `cantidad`) VALUES 
  (31,14,3,1,'Producto 1','sadf',12.00,32.00),
  (33,25,8,1,'Papas rellenas','hj',6.00,56.00),
  (34,26,8,1,'Papas rellenas','hji',45.00,6.00),
  (44,43,3,1,'Producto 1','asd',12.00,3.00);
COMMIT;

#
# Data for the `nompagos` table  (LIMIT 0,500)
#

INSERT INTO `nompagos` (`id`, `nombre`) VALUES 
  (1,'Tarjeta de Crédito'),
  (2,'Pago Online'),
  (3,'Efectivo');
COMMIT;

#
# Data for the `pagos` table  (LIMIT 0,500)
#

INSERT INTO `pagos` (`id`, `factura_id`, `metodo_id`, `fecha`, `nota`, `importe`) VALUES 
  (8,14,2,'2014-09-29','zxdff',400.00),
  (14,25,2,'2014-11-04','gtf',15.00),
  (15,43,1,'2014-09-30','hh',10.00),
  (16,43,3,'2014-11-05','ghjgh',29.60);
COMMIT;

#
# Data for the `usuario` table  (LIMIT 0,500)
#

INSERT INTO `usuario` (`id`, `nombre`, `email`, `password`, `salt`, `fechaAlta`, `compannia`, `direccion`, `telefono`, `fax`, `movil`, `webpage`, `cliente_id`) VALUES 
  (3,'Kepnix Capital E.I.R.L','demo@kepnix.com','OoQ1qSm6ejBQumc4S6qYAFOGaDpfzIpDJrF+4S1g0w7iHZqwx2FPREXuCseLtPqjdWhWuEoxAkD4N6fPtJjk2Q==','07689eba0c91622a340e5cce8a9e25d3','2014-09-29','Kepnix Capital E.I.R.L','Algún lugar en Perú.',22332121,5654,521685465,'ww.kepnix.com',NULL),
  (4,'Fulana De Tal','asd@asd.asd','taEufzVndXCycF46h1kilZCMLObIvRCHST3UGkXY79xQFC6kqC4anNHu1Db2GuQQzJ7kGdvEqa//aU20HWlTHA==','b3c1b97e0b8549e8d13ca246ef0018b0','2014-09-30',NULL,'asdasd',232223,234234,234,'www.ss.com',2);
COMMIT;



/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;