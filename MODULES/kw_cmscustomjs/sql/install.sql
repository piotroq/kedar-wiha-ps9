-- KEDAR-WIHA.pl — kw_cmscustomjs :: Install SQL

CREATE TABLE IF NOT EXISTS `PREFIX_kw_cms_custom_js` (
    `id_kw_cms_custom_js` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `id_cms` INT(11) UNSIGNED NOT NULL,
    `custom_js` LONGTEXT DEFAULT NULL,
    `date_add` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `date_upd` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id_kw_cms_custom_js`),
    UNIQUE KEY `idx_id_cms` (`id_cms`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
