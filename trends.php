<?php
/**
 * Plugin Name: Trends
 * Description: This is a trends topics  plugins jetpack extension
 * Version:     1.0
 * Author:      eufelipemateus
 * Author URI:  http://eufelipemateus.com
 * License:     GPLv2 or later
 * Text Domain: eufelipemateus
 */


if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly;
}


function head(){
    ?>
<style>
    #trends a{
        color: #000;
        font-weight: bolder;
        text-decoration: none;
    }

</style>
<?php
}


function get_posts_url(){
    $html = '';
    $top_posts = stats_get_csv( 'postviews', 'period=week&limit=10' );
    foreach ($top_posts as $post_item):
        $html .= '<li>
            <a href="'.$post_item['post_permalink'].'" class="special-slide">
            '.$post_item['post_title'].' 
            </a>
            </li>';
    endforeach; 
    return $html;
    
}


function trends(){
    return '<ol id="trends">
            '.get_posts_url().'
        </ol>';
}


add_action('wp_head', 'head');
add_shortcode('trends', 'trends');

function wordcloud_shortcode($atts) {
    $stopwords = [
        'de','a','o','que','e','do','da','em','um','para','é','com','não','uma','os','no','se','na','por','mais','as','dos','como','mas','foi','ao','ele','das','tem','à','seu','sua','ou','ser','quando','muito','há','nos','já','está','eu','também','só','pelo','pela','até','isso','ela','entre','era','depois','sem','mesmo','aos','ter','seus','quem','nas','me','esse','eles','estão','você','tinha','foram','essa','num','nem','suas','meu','às','minha','têm','numa','pelos','elas','havia','seja','qual','será','nós','tenho','lhe','deles','essas','esses','pelas','este','dele','tu','te','vocês','vos','lhes','meus','minhas','teu','tua','teus','tuas','nosso','nossa','nossos','nossas','dela','delas','esta','estes','estas','aquele','aquela','aqueles','aquelas','isto','aquilo','estou','está','estamos','estão','estive','esteve','estivemos','estiveram','estava','estávamos','estavam','estivera','estivéramos','esteja','estejamos','estejam','estivesse','estivéssemos','estivessem','estiver','estivermos','estiverem','hei','há','havemos','hão','houve','houvemos','houveram','houvera','houvéramos','haja','hajamos','hajam','houvesse','houvéssemos','houvessem','houver','houvermos','houverem','houverei','houverá','houveremos','houverão','houveria','houveríamos','houveriam','sou','somos','são','era','éramos','eram','fui','foi','fomos','foram','fora','fôramos','seja','sejamos','sejam','fosse','fôssemos','fossem','for','formos','forem','serei','será','seremos','serão','seria','seríamos','seriam','tenho','tem','temos','tém','tinha','tínhamos','tinham','tive','teve','tivemos','tiveram','tivera','tivéramos','tenha','tenhamos','tenham','tivesse','tivéssemos','tivessem','tiver','tivermos','tiverem','terei','terá','teremos','terão','teria','teríamos','teriam', 'contudo','pois','então','logo','assim','além','ainda','também','outra','outro','outras','outros','cada','certo','certos','certa','certas','qualquer','quaisquer','muito','muita','muitos','muitas','pouco','pouca','poucos','poucas','todo','toda','todos','todas','tal','tais', 'porém', 'pra','nbsp'
    ];

    // Busca todos os posts publicados
    $args = [
        'post_type' => 'post',
        'post_status' => 'publish',
        'posts_per_page' => -1,
        'fields' => 'ids',
    ];
    $query = new WP_Query($args);
    $all_text = '';
    if ($query->have_posts()) {
        foreach ($query->posts as $post_id) {
            $post = get_post($post_id);
            $all_text .= ' ' . $post->post_title . ' ' . $post->post_content;
        }
    }

    $all_text = mb_strtolower(strip_tags($all_text), 'UTF-8');
    $all_text = preg_replace('/[\d\W_]+/u', ' ', $all_text); // remove pontuação e números
    $words_array = preg_split('/\s+/u', $all_text, -1, PREG_SPLIT_NO_EMPTY);

    $words = [];
    foreach ($words_array as $word) {
        if (strlen($word) < 3) continue; // ignora palavras muito curtas
        if (in_array($word, $stopwords)) continue;
        if (!isset($words[$word])) {
            $words[$word] = 0;
        }
        $words[$word]++;
    }

    arsort($words);

    $words = array_slice($words, 0, 50, true);

    if (file_exists(__DIR__ . '/WordCloudSVG.php')) {
        require_once __DIR__ . '/WordCloudSVG.php';
        $cloud = new WordCloudSVG(800, 400);
        $cloud->setWords($words);
        return '<div style="overflow-x:auto;">' . $cloud->generate() . '</div>';
    } else {
        return '<p>Erro: WordCloudSVG.php não encontrado.</p>';
    }
}
add_shortcode('wordcloud', 'wordcloud_shortcode');
