-- ============================================================================
-- KEDAR-WIHA.pl — kw_cmscustomcss :: Install SQL
-- Tabela: PREFIX_kw_cms_custom_css
-- Przechowuje custom CSS per strona CMS.
-- Indeks UNIQUE na id_cms gwarantuje szybkie wyszukiwanie i brak duplikatów.
-- LONGTEXT obsługuje nawet bardzo rozbudowane style.
-- ============================================================================

CREATE TABLE IF NOT EXISTS `PREFIX_kw_cms_custom_css` (
    `id_kw_cms_custom_css` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `id_cms` INT(11) UNSIGNED NOT NULL,
    `custom_css` LONGTEXT DEFAULT NULL,
    `date_add` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `date_upd` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id_kw_cms_custom_css`),
    UNIQUE KEY `idx_id_cms` (`id_cms`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
