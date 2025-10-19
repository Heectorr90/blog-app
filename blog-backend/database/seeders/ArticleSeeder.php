<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Article;
use Illuminate\Database\Seeder;

class ArticleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $ADMIN_USER_ID = 1; // Asumiendo que el usuario admin tiene ID 1

        $articles = [
            [
                'title' => 'Introducción a Laravel 12',
                'slug' => 'introduccion-a-laravel-12',
                'excerpt' => 'Descubre las nuevas características y mejoras que trae Laravel 12 para el desarrollo web moderno.',
                'content' => '<h2>¿Qué es Laravel?</h2><p>Laravel es un framework de PHP que facilita el desarrollo de aplicaciones web robustas y escalables.</p><h3>Características principales</h3><ul><li>Sistema de routing elegante</li><li>ORM Eloquent potente</li><li>Sistema de migraciones</li><li>Autenticación incluida</li></ul><p>En Laravel 12 encontramos mejoras significativas en rendimiento y nuevas características que hacen el desarrollo aún más eficiente.</p>',
                'status' => 'published',
                'category_id' => 1, // Tecnología
                'user_id' => $ADMIN_USER_ID,
                'image' => 'articles/introduccion-a-laravel-12.jpg', // Nueva imagen
            ],
            [
                'title' => 'Guía Completa de Vue.js 3',
                'slug' => 'guia-completa-vue-js-3',
                'excerpt' => 'Todo lo que necesitas saber para empezar a desarrollar aplicaciones modernas con Vue.js 3 y Composition API.',
                'content' => '<h2>Vue.js 3: El Framework Progresivo</h2><p>Vue.js es uno de los frameworks JavaScript más populares para construir interfaces de usuario interactivas.</p><h3>Composition API</h3><p>La Composition API es una de las características más importantes de Vue 3, permitiendo organizar mejor el código y reutilizar lógica entre componentes.</p><pre><code>import { ref, computed } from "vue"\n\nconst count = ref(0)\nconst doubled = computed(() => count.value * 2)</code></pre>',
                'status' => 'published',
                'category_id' => 1, // Tecnología
                'user_id' => $ADMIN_USER_ID,
                'image' => 'articles/guia-completa-vue-js-3.jpg', // Nueva imagen
            ],
            [
                'title' => 'Principios de Diseño UI/UX',
                'slug' => 'principios-diseno-ui-ux',
                'excerpt' => 'Aprende los principios fundamentales del diseño de interfaces que todo diseñador debe conocer.',
                'content' => '<h2>Diseño Centrado en el Usuario</h2><p>El diseño UI/UX se enfoca en crear experiencias significativas y relevantes para los usuarios.</p><h3>Principios Clave</h3><ol><li><strong>Simplicidad:</strong> Menos es más</li><li><strong>Consistencia:</strong> Mantén patrones uniformes</li><li><strong>Jerarquía visual:</strong> Guía la atención del usuario</li><li><strong>Feedback:</strong> Informa sobre las acciones</li></ol><p>Estos principios son fundamentales para crear interfaces intuitivas y agradables.</p>',
                'status' => 'published',
                'category_id' => 2, // Diseño
                'user_id' => $ADMIN_USER_ID,
                'image' => 'articles/principios-diseno-ui-ux.jpg', // Nueva imagen
            ],
            [
                'title' => 'SEO en 2024: Estrategias que Funcionan',
                'slug' => 'seo-2024-estrategias',
                'excerpt' => 'Las mejores prácticas de SEO para posicionar tu sitio web en los primeros resultados de búsqueda.',
                'content' => '<h2>SEO Moderno</h2><p>El SEO ha evolucionado significativamente y ahora se enfoca más en la experiencia del usuario y contenido de calidad.</p><h3>Factores Clave de Ranking</h3><ul><li>Contenido de alta calidad y relevante</li><li>Velocidad de carga del sitio</li><li>Mobile-first indexing</li><li>Core Web Vitals</li><li>Enlaces de calidad</li></ul><p>Implementar estas estrategias te ayudará a mejorar tu posicionamiento orgánico.</p>',
                'status' => 'published',
                'category_id' => 4, // Marketing
                'user_id' => $ADMIN_USER_ID,
                'image' => 'articles/seo-2024-estrategias.jpg', // Nueva imagen
            ],
            [
                'title' => 'Cómo Iniciar un Negocio en Línea',
                'slug' => 'iniciar-negocio-en-linea',
                'excerpt' => 'Pasos prácticos para lanzar tu emprendimiento digital desde cero.',
                'content' => '<h2>Emprender en la Era Digital</h2><p>Iniciar un negocio en línea nunca ha sido tan accesible como ahora.</p><h3>Pasos para Comenzar</h3><ol><li>Identifica tu nicho de mercado</li><li>Investiga a tu competencia</li><li>Crea un plan de negocio</li><li>Desarrolla tu presencia online</li><li>Establece canales de venta</li><li>Implementa estrategias de marketing</li></ol><p>Con dedicación y las estrategias correctas, puedes construir un negocio exitoso en internet.</p>',
                'status' => 'published',
                'category_id' => 4, // Negocios
                'user_id' => $ADMIN_USER_ID,
                'image' => 'articles/iniciar-negocio-en-linea.jpg', // Nueva imagen
            ],
            [
                'title' => 'Tutorial: API REST con Laravel',
                'slug' => 'tutorial-api-rest-laravel',
                'excerpt' => 'Aprende a crear una API REST profesional paso a paso con Laravel y autenticación JWT.',
                'content' => '<h2>Construyendo una API REST</h2><p>En este tutorial aprenderás a crear una API RESTful completa con Laravel.</p><h3>Requisitos</h3><ul><li>PHP 8.2+</li><li>Composer</li><li>MySQL</li></ul><h3>Paso 1: Instalación</h3><pre><code>composer create-project laravel/laravel mi-api\ncd mi-api\nphp artisan serve</code></pre><h3>Paso 2: Configurar Base de Datos</h3><p>Edita el archivo .env con tus credenciales de base de datos.</p>',
                'status' => 'published',
                'category_id' => 5, // Tutoriales
                'user_id' => $ADMIN_USER_ID,
                'image' => 'articles/tutorial-api-rest-laravel.jpg', // Nueva imagen
            ],
            [
                'title' => 'El Futuro de la Inteligencia Artificial',
                'slug' => 'futuro-inteligencia-artificial',
                'excerpt' => 'Exploramos las tendencias y el impacto que tendrá la IA en los próximos años.',
                'content' => '<h2>IA: Transformando el Mundo</h2><p>La inteligencia artificial está revolucionando todas las industrias.</p><h3>Áreas de Impacto</h3><ul><li>Medicina y diagnóstico</li><li>Educación personalizada</li><li>Automatización industrial</li><li>Asistentes virtuales</li><li>Vehículos autónomos</li></ul><p>Estamos apenas en el comienzo de esta revolución tecnológica.</p>',
                'status' => 'draft',
                'category_id' => 3, // Tecnología
                'user_id' => $ADMIN_USER_ID,
                'image' => 'articles/futuro-inteligencia-artificial.jpg', // Nueva imagen
            ],
            [
                'title' => 'Tendencias de Diseño Web 2024',
                'slug' => 'tendencias-diseno-web-2024',
                'excerpt' => 'Las últimas tendencias en diseño web que marcarán pauta este año.',
                'content' => '<h2>Diseño Web Moderno</h2><p>El diseño web continúa evolucionando con nuevas tendencias visuales y de interacción.</p><h3>Tendencias Principales</h3><ol><li>Diseño minimalista y limpio</li><li>Modo oscuro</li><li>Animaciones sutiles</li><li>Tipografía grande y atrevida</li><li>Ilustraciones personalizadas</li></ol><p>Incorporar estas tendencias mantendrá tu sitio web actual y atractivo.</p>',
                'status' => 'published',
                'category_id' => 2, // Diseño
                'user_id' => $ADMIN_USER_ID,
                'image' => 'articles/tendencias-diseno-web-2024.jpg', // Nueva imagen
            ],
        ];
        foreach ($articles as $articleData) {
            Article::firstOrCreate(
                ['slug' => $articleData['slug']],
                $articleData
            );
        }
    }
}
