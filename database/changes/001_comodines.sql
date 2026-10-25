-- 001 — Recompensas opcionales ("comodines"). PROPUESTA: no aplicada. La aplica el dueño a mano.
-- Diseño en docs/PLAN.md, sección 9; decisiones del dueño del 2026-10-24 en docs/specs/fase-3-*.md y fase-4-*.md.
-- Probar en ambas bases: cursos_el_mai y cursos_el_mai_test (y en Railway cuando toque).

-- Topes por curso, los define el ponente: comodines disponibles a la vez por alumno y concedidos en total por alumno.
ALTER TABLE courses
  ADD COLUMN reward_max_available TINYINT UNSIGNED NOT NULL DEFAULT 3 AFTER unlock_mode,
  ADD COLUMN reward_max_granted   TINYINT UNSIGNED NOT NULL DEFAULT 5 AFTER reward_max_available;

-- Reglas que configura el ponente por curso. Sin reglas activas, no existe nada visible para el alumno.
CREATE TABLE reward_rules (
  id               BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  course_id        BIGINT UNSIGNED NOT NULL,
  streak_length    TINYINT UNSIGNED NOT NULL,                -- N tareas calificadas consecutivas
  min_score_pct    DECIMAL(5,2) NOT NULL,                    -- X: nota mínima en % del máximo de cada tarea
  reward_kind      ENUM('skip_task','bonus_points','extra_exam_attempt','extension_24h') NOT NULL,
  is_active        TINYINT(1) NOT NULL DEFAULT 1,
  created_by       BIGINT UNSIGNED NOT NULL,
  created_at       DATETIME NULL,
  updated_at       DATETIME NULL,
  PRIMARY KEY (id),
  KEY idx_reward_rules_course (course_id, is_active),
  CONSTRAINT fk_reward_rules_course  FOREIGN KEY (course_id)  REFERENCES courses (id) ON DELETE CASCADE,
  CONSTRAINT fk_reward_rules_creator FOREIGN KEY (created_by) REFERENCES users (id),
  CONSTRAINT chk_reward_rules_streak CHECK (streak_length BETWEEN 1 AND 50),
  CONSTRAINT chk_reward_rules_pct    CHECK (min_score_pct BETWEEN 0 AND 100)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Comodines concedidos. La racha que lo ganó termina en last_submission_id; la siguiente racha
-- empieza después de esa entrega (así una misma racha no concede dos veces).
CREATE TABLE reward_grants (
  id                 BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  rule_id            BIGINT UNSIGNED NOT NULL,
  user_id            BIGINT UNSIGNED NOT NULL,
  last_submission_id BIGINT UNSIGNED NOT NULL,
  status             ENUM('available','used','revoked') NOT NULL DEFAULT 'available',
  granted_at         DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  used_at            DATETIME NULL,
  used_task_id       BIGINT UNSIGNED NULL,                   -- skip_task, extension_24h, bonus_points
  bonus_points       DECIMAL(5,2) NULL,                      -- bonus_points: lo aplicado (hasta 10 % del máximo, sin pasar del máximo)
  used_exam_id       BIGINT UNSIGNED NULL,                   -- extra_exam_attempt
  revoked_at         DATETIME NULL,
  revoked_by         BIGINT UNSIGNED NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_reward_grant_streak (rule_id, user_id, last_submission_id),
  KEY idx_reward_grants_user (user_id, status),
  CONSTRAINT fk_reward_grants_rule       FOREIGN KEY (rule_id)            REFERENCES reward_rules (id) ON DELETE CASCADE,
  CONSTRAINT fk_reward_grants_user       FOREIGN KEY (user_id)            REFERENCES users (id),
  CONSTRAINT fk_reward_grants_submission FOREIGN KEY (last_submission_id) REFERENCES submissions (id) ON DELETE CASCADE,
  CONSTRAINT fk_reward_grants_task       FOREIGN KEY (used_task_id)       REFERENCES tasks (id) ON DELETE SET NULL,
  CONSTRAINT fk_reward_grants_exam       FOREIGN KEY (used_exam_id)       REFERENCES exams (id) ON DELETE SET NULL,
  CONSTRAINT fk_reward_grants_revoker    FOREIGN KEY (revoked_by)         REFERENCES users (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- cursos_app ya tiene SELECT/INSERT/UPDATE/DELETE sobre ambas bases (nivel base de datos): no hacen falta GRANT nuevos.
