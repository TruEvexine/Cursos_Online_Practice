# Benchmark: qué hacen bien y de qué se quejan (Google Classroom, Canva para Educación, Moodle, Teachable/Hotmart/Udemy)

Investigación del 2026-10-26, antes del primer código de la Fase 3.

## Método y límites

- **Búsquedas:** en español e inglés, en reseñas públicas (Capterra, GetApp, TrustRadius, Trustpilot, SoftwareFinder), foros oficiales (moodle.org, comunidades de Canvas, Blackbaud y D2L), documentación oficial, periódicos escolares y encuestas universitarias.
- **Reddit no se pudo consultar:** su sitio bloquea a la herramienta de búsqueda. No hay hilos de r/Teachers, r/edtech ni r/GoogleClassroom. Es el hueco principal de este documento.
- **No entré a contenido tras login** ni a tiendas de apps; los comentarios de apps se tomaron de agregadores públicos.
- **Todo está parafraseado.** Los textos de las reseñas llegaron a través del buscador, así que no los cito textualmente para no atribuir palabras inexactas. Cada fila enlaza sus fuentes.
- **Confianza:**
  - **alta**: 3 o más fuentes independientes;
  - **media**: 2;
  - **baja**: 1, o fuentes viejas o anecdóticas.

  Muchas fuentes son de años anteriores; puede que el producto ya lo haya corregido.
- **Prioridad, en modo ponytail para un proyecto chico:**
  - **P1**: entra en la Fase 3 o en la DEMO;
  - **P2**: siguiente fase que toque;
  - **P3**: idea, sin fecha.

## Tabla

| # | Queja o acierto | Fuentes | Confianza | Rol | ¿Lo cubre Gaining Grades? | Acción propuesta | Prioridad |
|---|---|---|---|---|---|---|---|
| 1 | **Queja:** las fechas de entrega se pierden entre las publicaciones; cuesta saber qué vence y cuándo. | [Periódico escolar sobre Classroom](https://hiseye.org/4234/his-eye/features/notification-youve-got-homework-students-and-teachers-discuss-google-classroom-updates/) · [Oxford Brookes sobre Moodle](https://www.brookes.ac.uk/students/news/2024/03/your-feedback-on-the-new-moodle) · [Comunidad Canvas](https://community.canvaslms.com/t5/Idea-Conversations/Instructor-to-do-list/m-p/542533) · [Comunidad D2L](https://community.d2l.com/brightspace/discussions?tagID=127) | alta (4) | alumno | parcial (hay fechas del curso; aún no hay tareas) | "Próxima entrega" y lista de pendientes por curso en "Mis cursos" y en el temario, con fecha en `America/Mexico_City`. | **P1** |
| 2 | **Queja:** la retroalimentación del profesor no le llega al alumno o no queda junto a la nota. | [Comunidad Blackbaud (Google Assignments)](https://community.blackbaud.com/discussion/comment/241227/) · [mvstampede: errores al pasar notas](https://mvstampede.net/6770/oped/infinite-campus-vs-google-classroom-the-struggle-of-the-softwares) | media (2) | alumno, profesor | no (Fase 3) | Retroalimentación guardada en la misma entrega y mostrada junto a la nota; una sola fuente de calificaciones, sin sincronizar con otro sistema. | **P1** |
| 3 | **Acierto:** ver el trabajo faltante ("Missing") y, como profesor, cuántos entregaron, faltan o van tarde. | [Ayuda de Google (alumnos)](https://support.google.com/edu/classroom/answer/6020284) · [Blog de Google: consejos](https://blog.google/products/classroom/google-classroom-tips-teachers/) · [TrustRadius: aviso de entrega tarde](https://trustradius.com/reviews/google-classroom-2016-05-03-14-02-39) | alta (3) | alumno, profesor | no (Fase 3) | El alumno ve "pendiente / entregada / calificada / devuelta / tarde". La bandeja del ponente marca "tarde" y cuenta pendientes por tarea. | **P1** |
| 4 | **Queja:** navegación confusa e inconsistente entre cursos; los alumnos se pierden. | [Especificación de navegación de Moodle](https://docs.moodle.org/dev/Navigation_overhaul_specification) · [UCL](https://www.ucl.ac.uk/teaching-learning/case-studies/2016/feb/using-moodle-bring-departments-and-students-together) · [Encuesta LSE (PDF)](https://info.lse.ac.uk/current-students/part-of-lse/assets/documents/Change-makers/research-archive/2019-20/31-Moodle.pdf) · [Encuesta Dundee](https://pgmed.dundee.ac.uk/mod/forum/discuss.php?d=39527) | alta (4) | alumno | sí | Mantener una sola estructura (curso → bloque → tema), migas de pan y tema anterior/siguiente. El mapa de misiones debe seguir ese mismo orden. | P3 (mantener) |
| 5 | **Queja:** interfaz anticuada, saturada de enlaces y con curva de aprendizaje. | [Capterra Moodle](https://capterra.com/p/80691/Moodle/reviews/) · [SoftwareFinder Moodle](https://softwarefinder.com/lms/moodle-software/reviews?page=2) · [eLearning Industry Moodle](https://elearningindustry.com/directory/elearning-software/moodle/reviews) | alta (3) | ambos | sí | Seguir con `docs/DESIGN.md` y estados vacíos con una acción. | P3 (mantener) |
| 6 | **Queja:** versión móvil mal adaptada o distinta a la de escritorio. | [Capterra Moodle](https://www.capterra.com/p/80691/Moodle/reviews/?page=2) · [Oxford Brookes](https://www.brookes.ac.uk/students/news/2024/03/your-feedback-on-the-new-moodle) | media (2) | alumno | sí (responsiva, la misma app) | Revisar a 375 px cada pantalla nueva (ya está en la auditoría). | P3 (mantener) |
| 7 | **Queja:** exámenes con tiempo que pierden respuestas o no se envían solos al acabarse el tiempo. | [Foro Moodle d=230242](https://moodle.org/mod/forum/discuss.php?d=230242) · [Foro Moodle d=217504](https://moodle.org/mod/forum/discuss.php?d=217504) · [MoodleDocs: Quiz Time Limit](https://docs.moodle.org/32/en/Quiz_Time_Limit) · [MDL-74378](https://tracker-old.moodle.org/browse/MDL-74378) | alta (4, versiones viejas) | alumno | plan (Fase 4) | Guardar cada respuesta al contestarla, enviar lo guardado al acabarse el tiempo en el servidor (no depender del JS) y probarlo con JS apagado. | P2 (Fase 4) |
| 8 | **Queja:** avisos poco confiables, tardíos o a deshoras. | [DigiStatement (2021)](https://digistatement.com/google-classroom-notifications-not-working-for-many-users-in-2021-fix-awaited/) · [JustUseApp](https://justuseapp.com/en/app/924620788/google-classroom/reviews) · [Periódico escolar](https://hiseye.org/4234/his-eye/features/notification-youve-got-homework-students-and-teachers-discuss-google-classroom-updates/) | media (3, anecdóticas) | alumno | no (no hay avisos) | No depender de avisos: lo pendiente siempre visible al entrar (fila 1). Correo opcional después. | P3 |
| 9 | **Acierto:** rúbricas y banco de comentarios para calificar rápido. | [Blog de Google](https://blog.google/products/classroom/google-classroom-tips-teachers/) · [THE Journal (2019)](https://thejournal.com/Articles/2019/06/19/Google-Classroom-Gets-Enhanced-Grading-and-Feedback-Tools.aspx) | media (2) | profesor | no | Fuera de la Fase 3 (la spec ya excluye rúbricas). Idea: comentarios frecuentes reutilizables por ponente. | P3 |
| 10 | **Queja:** el alumno solo entra si el profesor lo invita (Canva para Educación); o necesita cuenta de Google. | [ecomm.design (Canva)](https://ecomm.design/canva-for-education/) · [HP (Classroom)](https://www.hp.com/cl-es/tech-takes/educacion/resenas/google-classroom-resena-educadores.html) | media (2) | alumno | parcial (invitación por correo, sin cuenta de terceros) | Registro abierto mínimo con edad y términos, más catálogo (hito DEMO). | **P1** |
| 11 | **Queja:** comisiones por transacción y funciones clave en planes caros. | [GetApp Teachable](https://www.getapp.com/education-childcare-software/a/teachable/reviews/?page=2) · [Blogging Guide](https://bloggingguide.com/teachable-review/) | media (2) | profesor | no aplica aún | Precios en `config/site.php`, simulador visible, sin cobros escondidos. Cuando haya cobros (8c), comisión explícita. | P3 |
| 12 | **Queja:** la plataforma impone descuentos y se queda con los datos de contacto de los alumnos. | [EzyCourse](https://ezycourse.com/blog/why-udemy-creators-are-frustrated) · [Comunidad Udemy](https://community.udemy.com/en/discussion/comment/151625) · [Ruzuku](https://www.ruzuku.com/compare/udemy-pricing) | media (3, blogs de competidores) | profesor | sí (el ponente fija su precio y ve a sus alumnos) | Mantener en 8c que el precio lo fija el ponente y que sus alumnos son suyos (exportar en una fase futura). | P3 |
| 13 | **Queja:** cobros, reembolsos y soporte lentos o confusos. | [Trustpilot Hotmart](https://uk.trustpilot.com/review/hotmart.com) · [Guía de Software](https://www.guiadesoftware.com/software/hotmart) · [Trustpilot Udemy](https://www.trustpilot.com/review/udemy.com?page=9) | alta (3) | alumno | parcial (pago simulado en la DEMO) | En 8c: estados claros de cada pago (solicitado, comprobante enviado, confirmado, rechazado), visibles al alumno y en la bitácora. | P2 (8c) |
| 14 | **Queja:** cursos desactualizados o mal estructurados. | [Trustpilot Udemy](https://www.trustpilot.com/review/udemy.com?page=5) | baja (1) | alumno | parcial | Mostrar "actualizado el…" en la ficha del catálogo. | P3 |
| 15 | **Queja:** depende de buena conexión; sin herramientas de comunicación directa. | [Capterra Google Classroom](https://www.capterra.com/p/186631/Google-Classroom/reviews/?page=6) · [Crehana](https://www.crehana.com/blog/transformacion-digital/google-classroom/) | media (2) | ambos | parcial (páginas ligeras; chat en la Fase 5) | Seguir sin JS pesado; la clase en vivo y el chat ya están en la Fase 5. | P3 |

## Lo que pasa al roadmap (PLAN)

- **P1 (Fase 3 / DEMO):**
  - "próxima entrega" y pendientes por curso (filas 1 y 3);
  - retroalimentación junto a la nota en la misma entrega (fila 2);
  - estado de cada entrega visible para el alumno y "tarde" en la bandeja (fila 3);
  - registro abierto mínimo y catálogo (fila 10).
- **P2:**
  - guardado por respuesta y envío automático en el servidor, probado sin JS (fila 7, Fase 4);
  - estados de pago claros y en la bitácora (fila 13, Fase 8c).
