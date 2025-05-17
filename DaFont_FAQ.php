<?php
session_start();
require 'BACK/DB_connection.php'; // Para el header, si necesita $conn

// Variables para el usuario que está visitando la página (desde la sesión)
$visitor_user_id = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;
$visitor_user_name = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : null;

// Contenido del FAQ (igual que antes, puedes modificarlo según necesites)
$faq_items = [
    [
        'question' => '¿Cómo instalo una fuente?',
        'answer' => '<p>La forma de instalar una fuente varía ligeramente según tu sistema operativo:</p>
                     <ul>
                         <li><strong>Windows:</strong> Haz clic derecho en el archivo de la fuente (generalmente .ttf o .otf) y selecciona "Instalar". Alternativamente, puedes copiar el archivo de la fuente a la carpeta "Fuentes" dentro de tu Panel de Control (o C:\Windows\Fonts).</li>
                         <li><strong>Mac OS X:</strong> Haz doble clic en el archivo de la fuente y haz clic en el botón "Instalar fuente" en la ventana que aparece. También puedes arrastrar el archivo a la carpeta "Fuentes" (~/Library/Fonts para el usuario actual, o /Library/Fonts para todos los usuarios).</li>
                         <li><strong>Linux:</strong> La forma más común es copiar los archivos .ttf o .otf a una carpeta de fuentes del sistema como <code>/usr/share/fonts/</code> (para todos los usuarios) o a una carpeta <code>.fonts/</code> en tu directorio personal (<code>~/.fonts/</code>) y luego actualizar la caché de fuentes con el comando <code>fc-cache -fv</code>.</li>
                     </ul>
                     <p>Después de instalar, puede que necesites reiniciar las aplicaciones donde quieras usar la nueva fuente para que aparezca en el listado.</p>'
    ],
    [
        'question' => 'La fuente que instalé no aparece en mi programa (Photoshop, Word, etc.)',
        'answer' => '<p>Asegúrate de haber cerrado y vuelto a abrir el programa después de instalar la fuente. Algunos programas solo cargan las fuentes disponibles al iniciarse.</p>
                     <p>Si sigue sin aparecer:</p>
                     <ul>
                         <li>Verifica que la fuente se haya instalado correctamente en la carpeta de fuentes de tu sistema.</li>
                         <li>Asegúrate de que el archivo de la fuente no esté corrupto. Intenta descargarlo de nuevo.</li>
                         <li>Algunas fuentes muy antiguas o mal creadas pueden no ser compatibles con todos los programas.</li>
                     </ul>'
    ],
    [
        'question' => '¿Qué significan las licencias de las fuentes (Freeware, Shareware, Dominio Público, etc.)?',
        'answer' => '<p>Es crucial entender la licencia de una fuente antes de usarla, especialmente para proyectos comerciales.</p>
                     <ul>
                         <li><strong>Freeware / Gratis para uso personal:</strong> Estas fuentes se pueden usar libremente para proyectos personales no comerciales. Para uso comercial (logos, productos a la venta, publicidad), generalmente necesitas contactar al autor para obtener una licencia comercial, que puede tener un costo.</li>
                         <li><strong>Shareware:</strong> Puedes probar la fuente, a menudo con un conjunto de caracteres limitado o por un tiempo limitado. Para uso continuado o completo, necesitas pagar una licencia.</li>
                         <li><strong>Demo:</strong> Similar al shareware, es una versión de prueba. Usualmente no incluye todos los caracteres o estilos y es solo para evaluación.</li>
                         <li><strong>Dominio Público:</strong> Estas fuentes no tienen restricciones de copyright y puedes usarlas libremente para cualquier propósito, personal o comercial.</li>
                         <li><strong>100% Gratis:</strong> Generalmente significa que la fuente es gratuita para uso personal y comercial, pero siempre es bueno verificar si el autor indica alguna condición específica (como dar crédito).</li>
                         <li><strong>Otras Licencias (Ej: SIL OFL, Apache):</strong> Son licencias de software libre que permiten uso, modificación y distribución bajo ciertas condiciones (a menudo requieren mantener la licencia original o no vender la fuente por sí misma).</li>
                     </ul>
                     <p><strong>Importante:</strong> La licencia que se muestra en nuestro sitio es una indicación. Siempre revisa los archivos "Readme" o "License" incluidos en el ZIP de la fuente, o la página web del autor para obtener los detalles exactos. En caso de duda, contacta al autor.</p>'
    ],
    [
        'question' => '¿Puedo usar las fuentes de este sitio para mi logo o para fines comerciales?',
        'answer' => '<p>Depende enteramente de la licencia de cada fuente individual. Como se mencionó arriba, muchas fuentes son "gratis para uso personal" únicamente. Si la licencia no indica explícitamente que es gratis para uso comercial (o es "Dominio Público", "100% Gratis" o una licencia libre permisiva como OFL), DEBES contactar al autor para adquirir una licencia comercial o obtener permiso.</p>
                     <p>Usar una fuente sin la licencia adecuada para fines comerciales puede tener consecuencias legales.</p>'
    ],
    [
        'question' => '¿Cómo puedo enviar una fuente a este sitio?',
        'answer' => '<p>Actualmente, la funcionalidad para que los usuarios envíen fuentes está en desarrollo. ¡Vuelve pronto para más actualizaciones!</p>
                     <p>Si eres un diseñador de fuentes y estás interesado en colaborar, puedes ponerte en contacto con nosotros a través de <a href="mailto:contacto@dafontclone.com">contacto@dafontclone.com</a> (reemplaza con tu email real).</p>'
    ],
    [
        'question' => 'Algunos caracteres (acentos, símbolos) no aparecen o se ven diferentes.',
        'answer' => '<p>No todas las fuentes incluyen todos los caracteres para todos los idiomas o todos los símbolos especiales (como el símbolo de euro €, copyright ©, etc.).</p>
                     <ul>
                         <li>Muchas fuentes gratuitas, especialmente las decorativas, solo incluyen el alfabeto básico inglés y números.</li>
                         <li>Antes de descargar, intenta previsualizar la fuente en nuestro sitio con el texto que necesitas para ver si los caracteres están disponibles.</li>
                         <li>Si un carácter específico no está en la fuente, tu sistema operativo o el software que estés usando intentará sustituirlo con un carácter de una fuente de sistema similar, lo que puede causar que se vea diferente.</li>
                     </ul>'
    ],
    [
        'question' => '¿Cómo contacto con los administradores del sitio o con un autor?',
        'answer' => '<p>Para contactar con el autor de una fuente específica, generalmente puedes encontrar un enlace a su página web o información de contacto en la página de detalles de la fuente (si el autor la ha proporcionado) o en los archivos "readme" dentro del ZIP de la fuente.</p>
                     <p>Para contactar con los administradores de DaFont Clone (nombre de ejemplo), puedes enviarnos un correo a <a href="mailto:admin@dafontclone.com">admin@dafontclone.com</a> (reemplaza con tu email real).</p>'
    ]
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Preguntas Frecuentes (FAQ) - DaFont</title>
    <link rel="stylesheet" href="CSS/style.css">
    <link rel="stylesheet" href="CSS/faq.css"> <script src="https://kit.fontawesome.com/093074d40c.js" crossorigin="anonymous"></script>
    
</head>
<body>

<header>
    <nav class="navbar">
        <div>
            <a href="DaFont_index.php" class="logo"><img id="navImg" src="Dafont1-Dark1.png" alt="Logo pagina Dafont"></a>
        </div>
        <ul class="nav-links" id="navMenu">
            <button id="closeMenu"><i class="fa fa-close"></i></button>
            <?php
            // $conn ya debería estar disponible
            $sql_categories_header = "SELECT nombreCategoria FROM Categorias ORDER BY nombreCategoria";
            $result_categories_header = mysqli_query($conn, $sql_categories_header);
            $categories_for_header = [];
            if ($result_categories_header && mysqli_num_rows($result_categories_header) > 0) {
                while ($cat_row_header = mysqli_fetch_assoc($result_categories_header)) {
                    $categories_for_header[] = $cat_row_header['nombreCategoria'];
                }
            }
            $subcategories_map_header = [
                'Fantasia' => ['Mágico', 'Épico', 'Oscuro'], 'Tecno' => ['Sci-Fi', 'Moderno', 'Futurista'],
                'Gotico' => ['Europeo', 'Medieval', 'Vampiro'], 'Basico' => ['Serif', 'Sans-Serif', 'Monospace'],
                'Script' => ['Caligrafía', 'Manuscrito', 'Firma'], 'Display' => ['Decorativa', 'Titular', 'Retro']
            ];
            foreach ($categories_for_header as $category_item_name_header) {
                echo '<li class="dropdown">';
                echo '<a href="DaFont_index.php?category=' . urlencode($category_item_name_header) . '" class="category-btn">' . htmlspecialchars($category_item_name_header) . '</a>';
                if (isset($subcategories_map_header[$category_item_name_header])) {
                    echo '<ul class="submenu">';
                    foreach ($subcategories_map_header[$category_item_name_header] as $subcategory_item_name_header) {
                        echo '<li><a href="DaFont_index.php?category=' . urlencode($category_item_name_header) . '&subcategory=' . urlencode($subcategory_item_name_header) . '">' . htmlspecialchars($subcategory_item_name_header) . '</a></li>';
                    }
                    echo '</ul>';
                }
                echo '</li>';
            }
            ?>
            <li>
                <form action="DaFont_index.php" method="GET" class="search-container-form">
                    <div class="search-container">
                        <input type="text" name="search_term" class="search-bar" placeholder="Buscar fuentes..." value="<?php echo isset($_GET['search_term']) ? htmlspecialchars($_GET['search_term']) : ''; ?>">
                        <button type="submit" class="search-button"><i class="fa fa-search"></i></button>
                    </div>
                </form>
            </li>
        </ul>
        <button id="btnSesion" 
            <?php if($visitor_user_id === 0): ?>
            onclick="window.location.href='Dafont_Log.php'">
            <i class="fa-solid fa-circle-user"></i></button>
            <?php else: ?>
            onclick="window.location.href='Dafont_Profile.php'">
            <?php echo htmlspecialchars($visitor_user_name); ?></button>
            <?php endif; ?>
        <div class="menu-hamburguesa">
            <span></span>
            <span></span>
            <span></span>
        </div>
    </nav>
</header>

<main>
    <nav id="breadcrumb">
        <span><a href="DaFont_index.php">Inicio</a></span>
        <span>FAQ (Preguntas Frecuentes)</span>
    </nav>

    <div class="faq-container">
        <h1 class="page-title-faq">Preguntas Frecuentes</h1>

        <?php if (!empty($faq_items)): ?>
            <?php foreach ($faq_items as $index => $item): // Añadimos $index para generar IDs únicos ?>
                <div class="faq-item">
                    <h2 class="faq-question" id="faq-question-<?php echo $index; ?>" role="button" aria-expanded="false" aria-controls="faq-answer-<?php echo $index; ?>">
                        <?php echo htmlspecialchars($item['question']); ?>
                        <span class="faq-toggler" aria-hidden="true"><i class="fa fa-chevron-down"></i></span>
                    </h2>
                    <div class="faq-answer" id="faq-answer-<?php echo $index; ?>" role="region" aria-labelledby="faq-question-<?php echo $index; ?>">
                        <?php echo $item['answer']; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No hay preguntas frecuentes disponibles en este momento.</p>
        <?php endif; ?>
    </div>
</main>

<footer>
    <p> Las fuentes presentadas en este sitio web son propiedad de sus autores...</p>
    <p><a href="DaFont_FAQ.php">FAQ</a></p>
    <p><a href="DaFont_AuthorsList.php">Autores</a></p>
</footer>

<script src="JS/app.js"></script>
<script src="JS/script.js"></script>
<script src="JS/breadcrumbing.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const questions = document.querySelectorAll('.faq-question');

        questions.forEach(question => {
            const answer = document.getElementById(question.getAttribute('aria-controls'));
            const togglerIcon = question.querySelector('.faq-toggler i');

            // Asegurarse de que el estilo inicial permita la transición
            if(answer){ // Verificar que 'answer' exista
                answer.style.maxHeight = '0'; // Colapsado explícitamente
                answer.style.opacity = '0';
                answer.style.overflow = 'hidden';
                answer.style.paddingTop = '0';
                answer.style.paddingBottom = '0';
                answer.style.marginTop = '0'; // Consistencia con el estado colapsado
                answer.style.transition = 
                    'max-height 0.35s ease-in-out, ' +
                    'opacity 0.3s ease-in-out, ' +
                    'padding-top 0.3s ease-in-out, ' +
                    'padding-bottom 0.3s ease-in-out, ' +
                    'margin-top 0.3s ease-in-out';
            }


            question.addEventListener('click', () => {
                if(!answer) return; // Si no hay respuesta asociada, no hacer nada

                const isExpanded = question.getAttribute('aria-expanded') === 'true';

                if (isExpanded) {
                    // Colapsar
                    answer.style.maxHeight = '0';
                    answer.style.opacity = '0';
                    // Retrasar ligeramente la eliminación del padding para que no se vea brusco el corte
                    setTimeout(() => {
                        if (question.getAttribute('aria-expanded') === 'false') { // Volver a chequear por si el usuario hizo clic rápido
                            answer.style.paddingTop = '0';
                            answer.style.paddingBottom = '0';
                            answer.style.marginTop = '0';
                        }
                    }, 300); // Un poco menos que la transición de max-height

                    question.setAttribute('aria-expanded', 'false');
                    if (togglerIcon) togglerIcon.classList.replace('fa-chevron-up', 'fa-chevron-down');
                } else {
                    // Expandir
                    answer.style.opacity = '1';
                    answer.style.paddingTop = '15px';
                    answer.style.paddingBottom = '15px';
                    answer.style.marginTop = '10px';
                    // Establecer maxHeight DESPUÉS de que el padding se aplique conceptualmente
                    // para que scrollHeight lo incluya (aunque en la práctica el navegador recalcula).
                    // Un pequeño buffer puede ayudar.
                    answer.style.maxHeight = (answer.scrollHeight + 20) + "px"; // Añadir un pequeño buffer (ej. 20px)

                    question.setAttribute('aria-expanded', 'true');
                    if (togglerIcon) togglerIcon.classList.replace('fa-chevron-down', 'fa-chevron-up');
                }
            });
        });
    });
</script>
</body>
</html>