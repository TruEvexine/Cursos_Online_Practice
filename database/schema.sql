-- Cursos el Mai — paso 2: tablas, relaciones, vistas y datos iniciales (v1).
-- Ejecutar después de 00_database_and_user.sql:
--   mysql -u root -p cursos_el_mai < database/schema.sql
--
-- Probado en MariaDB 10.11 / compatible con MySQL 8.0+ (InnoDB, utf8mb4).
-- Esta es la fuente de verdad del esquema. Los cambios futuros van como archivos
-- numerados en database/changes/ (001_*.sql, 002_*.sql...) y se aplican a mano.
-- Fechas en UTC; la app las muestra en America/Mexico_City.
-- Borrado: users y courses usan borrado lógico (deleted_at); el resto se borra en cascada
-- desde su padre. Las referencias a users son RESTRICT para no perder historial.

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ---------------------------------------------------------------------------
-- 1. Usuarios y roles
-- ---------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS roles (
                                     id    TINYINT UNSIGNED NOT NULL PRIMARY KEY,
                                     slug  VARCHAR(30) NOT NULL UNIQUE,
    name  VARCHAR(60) NOT NULL
    ) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS users (
                                     id                BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                                     role_id           TINYINT UNSIGNED NOT NULL,
                                     name              VARCHAR(120) NOT NULL,
    email             VARCHAR(190) NOT NULL UNIQUE,
    email_verified_at DATETIME NULL,
    password          VARCHAR(255) NOT NULL,
    remember_token    VARCHAR(100) NULL,
    is_active         TINYINT(1) NOT NULL DEFAULT 1,
    last_login_at     DATETIME NULL,
    created_at        DATETIME NULL,
    updated_at        DATETIME NULL,
    deleted_at        DATETIME NULL,
    KEY idx_users_role (role_id),
    CONSTRAINT fk_users_role FOREIGN KEY (role_id) REFERENCES roles (id)
    ) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS profiles (
                                        user_id      BIGINT UNSIGNED NOT NULL PRIMARY KEY,
                                        avatar_path  VARCHAR(255) NULL,
    headline     VARCHAR(120) NULL,
    bio          TEXT NULL,
    interests    VARCHAR(255) NULL,
    accent_color CHAR(7) NULL,          -- #RRGGBB, la app lo limita a una paleta aprobada
    website_url  VARCHAR(255) NULL,
    created_at   DATETIME NULL,
    updated_at   DATETIME NULL,
    CONSTRAINT fk_profiles_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
    ) ENGINE=InnoDB;

-- Tabla estándar de Laravel (Fortify) para "olvidé mi contraseña" e invitaciones.
CREATE TABLE IF NOT EXISTS password_reset_tokens (
                                                     email      VARCHAR(190) NOT NULL PRIMARY KEY,
    token      VARCHAR(255) NOT NULL,
    created_at DATETIME NULL
    ) ENGINE=InnoDB;

-- ---------------------------------------------------------------------------
-- 2. Cursos, inscripciones y contenido (curso > bloque > tema > tarea)
-- ---------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS courses (
                                       id          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                                       ponente_id  BIGINT UNSIGNED NOT NULL,
                                       title       VARCHAR(160) NOT NULL,
    slug        VARCHAR(180) NOT NULL UNIQUE,
    summary     VARCHAR(300) NULL,
    description TEXT NULL,
    cover_path  VARCHAR(255) NULL,
    status      ENUM('draft','published','archived') NOT NULL DEFAULT 'draft',
    unlock_mode ENUM('sequential','free') NOT NULL DEFAULT 'sequential', -- misiones: secuencial o libre
    starts_on   DATE NULL,
    ends_on     DATE NULL,
    catalog_price DECIMAL(8,2) NULL,                    -- catálogo: NULL = oculto, 0 = gratis, > 0 = precio MXN (changes/002)
    created_at  DATETIME NULL,
    updated_at  DATETIME NULL,
    deleted_at  DATETIME NULL,
    KEY idx_courses_ponente (ponente_id, status),
    CONSTRAINT fk_courses_ponente FOREIGN KEY (ponente_id) REFERENCES users (id),
    CONSTRAINT chk_courses_catalog_price CHECK (catalog_price IS NULL OR catalog_price >= 0)
    ) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS enrollments (
                                           id          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                                           course_id   BIGINT UNSIGNED NOT NULL,
                                           user_id     BIGINT UNSIGNED NOT NULL,                 -- siempre un alumno
                                           status      ENUM('active','removed','completed') NOT NULL DEFAULT 'active', -- "eliminar alumno" = removed (conserva historial)
    enrolled_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    removed_at  DATETIME NULL,
    UNIQUE KEY uq_enrollment (course_id, user_id),
    KEY idx_enrollments_user (user_id, status),
    CONSTRAINT fk_enrollments_course FOREIGN KEY (course_id) REFERENCES courses (id) ON DELETE CASCADE,
    CONSTRAINT fk_enrollments_user   FOREIGN KEY (user_id)   REFERENCES users (id)
    ) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS blocks (
                                      id          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                                      course_id   BIGINT UNSIGNED NOT NULL,
                                      title       VARCHAR(160) NOT NULL,
    description TEXT NULL,
    position    SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    created_at  DATETIME NULL,
    updated_at  DATETIME NULL,
    KEY idx_blocks_course (course_id, position),
    CONSTRAINT fk_blocks_course FOREIGN KEY (course_id) REFERENCES courses (id) ON DELETE CASCADE
    ) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS topics (
                                      id          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                                      block_id    BIGINT UNSIGNED NOT NULL,
                                      title       VARCHAR(160) NOT NULL,
    content     MEDIUMTEXT NULL,                          -- Markdown (se renderiza con HTML desactivado)
    position    SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    is_required TINYINT(1) NOT NULL DEFAULT 1,            -- cuenta para desbloquear lo siguiente
    created_at  DATETIME NULL,
    updated_at  DATETIME NULL,
    KEY idx_topics_block (block_id, position),
    CONSTRAINT fk_topics_block FOREIGN KEY (block_id) REFERENCES blocks (id) ON DELETE CASCADE
    ) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS materials (
                                         id            BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                                         course_id     BIGINT UNSIGNED NOT NULL,
                                         topic_id      BIGINT UNSIGNED NULL,                   -- NULL = material general del curso
                                         uploaded_by   BIGINT UNSIGNED NOT NULL,
                                         title         VARCHAR(160) NOT NULL,
    kind          ENUM('file','link','video') NOT NULL DEFAULT 'file',
    file_path     VARCHAR(255) NULL,                      -- ruta en almacenamiento PRIVADO, nombre aleatorio
    original_name VARCHAR(255) NULL,
    mime_type     VARCHAR(100) NULL,
    size_bytes    BIGINT UNSIGNED NULL,
    url           VARCHAR(500) NULL,
    created_at    DATETIME NULL,
    updated_at    DATETIME NULL,
    KEY idx_materials_course (course_id),
    KEY idx_materials_topic (topic_id),
    CONSTRAINT fk_materials_course FOREIGN KEY (course_id)   REFERENCES courses (id) ON DELETE CASCADE,
    CONSTRAINT fk_materials_topic  FOREIGN KEY (topic_id)    REFERENCES topics (id)  ON DELETE SET NULL,
    CONSTRAINT fk_materials_user   FOREIGN KEY (uploaded_by) REFERENCES users (id),
    CONSTRAINT chk_materials_source CHECK (
(kind = 'file' AND file_path IS NOT NULL) OR (kind <> 'file' AND url IS NOT NULL)
    )
    ) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS tasks (
                                     id           BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                                     topic_id     BIGINT UNSIGNED NOT NULL,
                                     title        VARCHAR(160) NOT NULL,
    instructions TEXT NULL,
    due_at       DATETIME NULL,
    max_score    DECIMAL(5,2) NOT NULL DEFAULT 100.00,
    weight       DECIMAL(5,2) NOT NULL DEFAULT 1.00,     -- peso en el promedio del curso
    is_required  TINYINT(1) NOT NULL DEFAULT 1,
    allow_late   TINYINT(1) NOT NULL DEFAULT 0,
    position     SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    created_at   DATETIME NULL,
    updated_at   DATETIME NULL,
    KEY idx_tasks_topic (topic_id, position),
    CONSTRAINT fk_tasks_topic FOREIGN KEY (topic_id) REFERENCES topics (id) ON DELETE CASCADE
    ) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS submissions (
                                           id            BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                                           task_id       BIGINT UNSIGNED NOT NULL,
                                           user_id       BIGINT UNSIGNED NOT NULL,
                                           body          TEXT NULL,
                                           file_path     VARCHAR(255) NULL,
    original_name VARCHAR(255) NULL,
    status        ENUM('submitted','graded','returned') NOT NULL DEFAULT 'submitted', -- returned = el ponente pide rehacer
    score         DECIMAL(5,2) NULL,
    feedback      TEXT NULL,
    submitted_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    graded_by     BIGINT UNSIGNED NULL,
    graded_at     DATETIME NULL,
    created_at    DATETIME NULL,
    updated_at    DATETIME NULL,
    UNIQUE KEY uq_submission (task_id, user_id),
    KEY idx_submissions_user (user_id),
    KEY idx_submissions_status (task_id, status),
    CONSTRAINT fk_submissions_task   FOREIGN KEY (task_id)   REFERENCES tasks (id) ON DELETE CASCADE,
    CONSTRAINT fk_submissions_user   FOREIGN KEY (user_id)   REFERENCES users (id),
    CONSTRAINT fk_submissions_grader FOREIGN KEY (graded_by) REFERENCES users (id),
    CONSTRAINT chk_submissions_score CHECK (score IS NULL OR score >= 0)
    ) ENGINE=InnoDB;

-- Progreso: una fila = el alumno completó el tema. La app la crea cuando se cumplen las reglas
-- (tema visto y tareas obligatorias entregadas). De aquí salen los desbloqueos de la misión.
CREATE TABLE IF NOT EXISTS topic_progress (
                                              user_id      BIGINT UNSIGNED NOT NULL,
                                              topic_id     BIGINT UNSIGNED NOT NULL,
                                              completed_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                                              PRIMARY KEY (user_id, topic_id),
    KEY idx_progress_topic (topic_id),
    CONSTRAINT fk_progress_user  FOREIGN KEY (user_id)  REFERENCES users (id),
    CONSTRAINT fk_progress_topic FOREIGN KEY (topic_id) REFERENCES topics (id) ON DELETE CASCADE
    ) ENGINE=InnoDB;

-- ---------------------------------------------------------------------------
-- 3. Exámenes
-- ---------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS exams (
                                     id                 BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                                     course_id          BIGINT UNSIGNED NOT NULL,
                                     block_id           BIGINT UNSIGNED NULL,
                                     created_by         BIGINT UNSIGNED NOT NULL,
                                     title              VARCHAR(160) NOT NULL,
    instructions       TEXT NULL,
    status             ENUM('draft','published','closed') NOT NULL DEFAULT 'draft',
    time_limit_minutes SMALLINT UNSIGNED NULL,
    available_from     DATETIME NULL,
    available_until    DATETIME NULL,
    max_attempts       TINYINT UNSIGNED NOT NULL DEFAULT 1,
    passing_score      DECIMAL(5,2) NULL,                 -- en puntos; NULL = sin mínimo
    weight             DECIMAL(5,2) NOT NULL DEFAULT 1.00,
    created_at         DATETIME NULL,
    updated_at         DATETIME NULL,
    KEY idx_exams_course (course_id, status),
    CONSTRAINT fk_exams_course  FOREIGN KEY (course_id)  REFERENCES courses (id) ON DELETE CASCADE,
    CONSTRAINT fk_exams_block   FOREIGN KEY (block_id)   REFERENCES blocks (id)  ON DELETE SET NULL,
    CONSTRAINT fk_exams_creator FOREIGN KEY (created_by) REFERENCES users (id)
    ) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS exam_questions (
                                              id         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                                              exam_id    BIGINT UNSIGNED NOT NULL,
                                              type       ENUM('single','multiple','true_false','open') NOT NULL,
    prompt     TEXT NOT NULL,
    points     DECIMAL(5,2) NOT NULL DEFAULT 1.00,
    position   SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    created_at DATETIME NULL,
    updated_at DATETIME NULL,
    KEY idx_questions_exam (exam_id, position),
    CONSTRAINT fk_questions_exam FOREIGN KEY (exam_id) REFERENCES exams (id) ON DELETE CASCADE
    ) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS exam_options (
                                            id          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                                            question_id BIGINT UNSIGNED NOT NULL,
                                            label       VARCHAR(500) NOT NULL,
    is_correct  TINYINT(1) NOT NULL DEFAULT 0,
    position    SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    KEY idx_options_question (question_id, position),
    CONSTRAINT fk_options_question FOREIGN KEY (question_id) REFERENCES exam_questions (id) ON DELETE CASCADE
    ) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS exam_attempts (
                                             id           BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                                             exam_id      BIGINT UNSIGNED NOT NULL,
                                             user_id      BIGINT UNSIGNED NOT NULL,
                                             attempt_no   TINYINT UNSIGNED NOT NULL DEFAULT 1,
                                             status       ENUM('in_progress','submitted','graded') NOT NULL DEFAULT 'in_progress',
    started_at   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    submitted_at DATETIME NULL,
    score        DECIMAL(6,2) NULL,                       -- puntos obtenidos
    graded_by    BIGINT UNSIGNED NULL,
    graded_at    DATETIME NULL,
    UNIQUE KEY uq_attempt (exam_id, user_id, attempt_no),
    KEY idx_attempts_user (user_id),
    CONSTRAINT fk_attempts_exam   FOREIGN KEY (exam_id)   REFERENCES exams (id) ON DELETE CASCADE,
    CONSTRAINT fk_attempts_user   FOREIGN KEY (user_id)   REFERENCES users (id),
    CONSTRAINT fk_attempts_grader FOREIGN KEY (graded_by) REFERENCES users (id)
    ) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS exam_answers (
                                            id             BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                                            attempt_id     BIGINT UNSIGNED NOT NULL,
                                            question_id    BIGINT UNSIGNED NOT NULL,
                                            answer_text    TEXT NULL,                             -- solo preguntas abiertas
                                            points_awarded DECIMAL(5,2) NULL,
    feedback       TEXT NULL,
    UNIQUE KEY uq_answer (attempt_id, question_id),
    KEY idx_answers_question (question_id),
    CONSTRAINT fk_answers_attempt  FOREIGN KEY (attempt_id)  REFERENCES exam_attempts (id)  ON DELETE CASCADE,
    CONSTRAINT fk_answers_question FOREIGN KEY (question_id) REFERENCES exam_questions (id) ON DELETE CASCADE
    ) ENGINE=InnoDB;

-- Opciones elegidas (single / multiple / true_false).
CREATE TABLE IF NOT EXISTS exam_answer_options (
                                                   answer_id BIGINT UNSIGNED NOT NULL,
                                                   option_id BIGINT UNSIGNED NOT NULL,
                                                   PRIMARY KEY (answer_id, option_id),
    KEY idx_answer_options_option (option_id),
    CONSTRAINT fk_ao_answer FOREIGN KEY (answer_id) REFERENCES exam_answers (id) ON DELETE CASCADE,
    CONSTRAINT fk_ao_option FOREIGN KEY (option_id) REFERENCES exam_options (id) ON DELETE CASCADE
    ) ENGINE=InnoDB;

-- ---------------------------------------------------------------------------
-- 4. Avisos, videollamadas y chat moderado
-- ---------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS announcements (
                                             id           BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                                             course_id    BIGINT UNSIGNED NOT NULL,
                                             author_id    BIGINT UNSIGNED NOT NULL,
                                             title        VARCHAR(160) NOT NULL,
    body         TEXT NOT NULL,                           -- Markdown
    is_pinned    TINYINT(1) NOT NULL DEFAULT 0,
    published_at DATETIME NULL,                           -- NULL = borrador
    created_at   DATETIME NULL,
    updated_at   DATETIME NULL,
    deleted_at   DATETIME NULL,
    KEY idx_announcements_course (course_id, is_pinned, published_at),
    CONSTRAINT fk_announcements_course FOREIGN KEY (course_id) REFERENCES courses (id) ON DELETE CASCADE,
    CONSTRAINT fk_announcements_author FOREIGN KEY (author_id) REFERENCES users (id)
    ) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS live_sessions (
                                             id           BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                                             course_id    BIGINT UNSIGNED NOT NULL,
                                             created_by   BIGINT UNSIGNED NOT NULL,
                                             title        VARCHAR(160) NOT NULL,
    room_name    VARCHAR(120) NOT NULL UNIQUE,            -- aleatorio e imposible de adivinar
    meeting_url  VARCHAR(500) NULL,                       -- solo si se usa una llamada externa (Meet/Zoom) en vez de Jitsi
    scheduled_at DATETIME NULL,
    started_at   DATETIME NULL,
    ended_at     DATETIME NULL,
    chat_mode    ENUM('off','open','moderated') NOT NULL DEFAULT 'off', -- moderated = el ponente aprueba cada mensaje
    created_at   DATETIME NULL,
    updated_at   DATETIME NULL,
    KEY idx_live_course (course_id, scheduled_at),
    CONSTRAINT fk_live_course  FOREIGN KEY (course_id)  REFERENCES courses (id) ON DELETE CASCADE,
    CONSTRAINT fk_live_creator FOREIGN KEY (created_by) REFERENCES users (id)
    ) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS chat_messages (
                                             id              BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                                             live_session_id BIGINT UNSIGNED NOT NULL,
                                             user_id         BIGINT UNSIGNED NOT NULL,
                                             body            VARCHAR(1000) NOT NULL,
    status          ENUM('visible','pending','hidden') NOT NULL DEFAULT 'visible',
    moderated_by    BIGINT UNSIGNED NULL,
    moderated_at    DATETIME NULL,
    created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY idx_chat_session (live_session_id, id),
    KEY idx_chat_status (live_session_id, status),
    CONSTRAINT fk_chat_session   FOREIGN KEY (live_session_id) REFERENCES live_sessions (id) ON DELETE CASCADE,
    CONSTRAINT fk_chat_user      FOREIGN KEY (user_id)         REFERENCES users (id),
    CONSTRAINT fk_chat_moderator FOREIGN KEY (moderated_by)    REFERENCES users (id)
    ) ENGINE=InnoDB;

-- ---------------------------------------------------------------------------
-- 5. Bitácora (para que director y super admin puedan monitorear)
-- ---------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS audit_logs (
                                          id          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                                          user_id     BIGINT UNSIGNED NULL,
                                          action      VARCHAR(60) NOT NULL,                     -- p. ej. grade.set, enrollment.remove, login
    entity_type VARCHAR(60) NULL,
    entity_id   BIGINT UNSIGNED NULL,
    meta        JSON NULL,
    ip          VARCHAR(45) NULL,
    created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY idx_audit_user (user_id, created_at),
    KEY idx_audit_entity (entity_type, entity_id),
    CONSTRAINT fk_audit_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE SET NULL
    ) ENGINE=InnoDB;

SET FOREIGN_KEY_CHECKS = 1;

-- ---------------------------------------------------------------------------
-- 6. Vista de calificaciones (solo lectura para el alumno)
--    Tareas calificadas + examen (mejor intento calificado). El promedio ponderado se calcula
--    sobre esta vista: SUM(score/max_score*weight) / SUM(weight).
-- ---------------------------------------------------------------------------

CREATE OR REPLACE VIEW v_gradebook AS
SELECT c.id AS course_id, s.user_id, 'task' AS item_type, t.id AS item_id, t.title,
       s.score AS score, t.max_score AS max_score, t.weight AS weight, s.graded_at AS graded_at
FROM submissions s
         JOIN tasks  t  ON t.id  = s.task_id
         JOIN topics tp ON tp.id = t.topic_id
         JOIN blocks b  ON b.id  = tp.block_id
         JOIN courses c ON c.id  = b.course_id
WHERE s.status = 'graded'
UNION ALL
SELECT e.course_id, a.user_id, 'exam', e.id, e.title,
       MAX(a.score),
       (SELECT SUM(q.points) FROM exam_questions q WHERE q.exam_id = e.id),
       e.weight, MAX(a.graded_at)
FROM exam_attempts a
         JOIN exams e ON e.id = a.exam_id
WHERE a.status = 'graded'
GROUP BY e.course_id, a.user_id, e.id, e.title, e.weight;

-- ---------------------------------------------------------------------------
-- 7. Datos iniciales
--    El super admin NO se inserta aquí (no se guarda ninguna clave en este archivo):
--    se crea con el comando de la app:  php artisan app:create-super-admin
-- ---------------------------------------------------------------------------

INSERT INTO roles (id, slug, name) VALUES
                                       (1, 'super_admin', 'Super administrador'),
                                       (2, 'director',    'Director'),
                                       (3, 'ponente',     'Ponente'),
                                       (4, 'alumno',      'Alumno')
    ON DUPLICATE KEY UPDATE name = VALUES(name);
