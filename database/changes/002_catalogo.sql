-- 002 — Catálogo público (hito DEMO). La aplica el dueño a mano en ambas bases (cursos_el_mai y cursos_el_mai_test)
-- y en Railway. schema.sql ya incluye la columna.
--   NULL = el curso no aparece en el catálogo · 0 = gratis · > 0 = precio en MXN (en la DEMO, pago simulado).

ALTER TABLE courses
  ADD COLUMN catalog_price DECIMAL(8,2) NULL AFTER ends_on,
  ADD CONSTRAINT chk_courses_catalog_price CHECK (catalog_price IS NULL OR catalog_price >= 0);
