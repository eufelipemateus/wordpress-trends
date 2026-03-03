<?php
/**
 * Plugin Name: Trends
 * Description: This is a trends topics  plugins jetpack extension
 * Version:     1.1.0
 * Author:      eufelipemateus
 * Author URI:  http://felipemateus.com
 * License:     GPLv2 or later
 * Text Domain: eufelipemateus-trends
 */



if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! class_exists( 'Trends_Plugin' ) ) {
    class Trends_Plugin {
        public static function init() {
            // Hooks principais
            add_action( 'admin_init', [ __CLASS__, 'require_jetpack' ] );
            add_shortcode( 'trends', [ __CLASS__, 'trends_shortcode' ] );
            add_shortcode( 'wordcloud', [ __CLASS__, 'wordcloud_shortcode' ] );
        }

        public static function require_jetpack() {
            if ( is_admin() && current_user_can( 'activate_plugins' ) ) {
                include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
                if ( ! is_plugin_active( 'jetpack/jetpack.php' ) ) {
                    deactivate_plugins( plugin_basename( __FILE__ ) );
                    add_action( 'admin_notices', function() {
                        echo '<div class="notice notice-error"><p>O plugin <strong>Trends</strong> requer o Jetpack ativo. O plugin foi desativado.</p></div>';
                    } );
                }
            }
        }


        public static function get_trending_posts_html() {
            $html = '';
            if ( function_exists( 'stats_get_csv' ) ) {
                $top_posts = stats_get_csv( 'postviews', 'period=week&limit=10' );
                foreach ( $top_posts as $post_item ) {
                    $html .= '<li><a href="' . esc_url( $post_item['post_permalink'] ) . '" class="special-slide">' . esc_html( $post_item['post_title'] ) . '</a></li>';
                }
            } else {
                $html .= '<li>Estatísticas do Jetpack não disponíveis.</li>';
            }
            return $html;
        }

        public static function trends_shortcode() {
            return '<ol id="trends" class="trends-list">' . self::get_trending_posts_html() . '</ol>';
        }

        public static function wordcloud_shortcode( $atts ) {
            $stopwords = [
                'de','a','o','que','e','do','da','em','um','para','é','com','não','uma','os','no','se','na','por','mais','as','dos','como','mas','foi','ao','ele','das','tem','à','seu','sua','ou','ser','quando','muito','há','nos','já','está','eu','também','só','pelo','pela','até','isso','ela','entre','era','depois','sem','mesmo','aos','ter','seus','quem','nas','me','esse','eles','estão','você','tinha','foram','essa','num','nem','suas','meu','às','minha','têm','numa','pelos','elas','havia','seja','qual','será','nós','tenho','lhe','deles','essas','esses','pelas','este','dele','tu','te','vocês','vos','lhes','meus','minhas','teu','tua','teus','tuas','nosso','nossa','nossos','nossas','dela','delas','esta','estes','estas','aquele','aquela','aqueles','aquelas','isto','aquilo','estou','está','estamos','estão','estive','esteve','estivemos','estiveram','estava','estávamos','estavam','estivera','estivéramos','esteja','estejamos','estejam','estivesse','estivéssemos','estivessem','estiver','estivermos','estiverem','hei','há','havemos','hão','houve','houvemos','houveram','houvera','houvéramos','haja','hajamos','hajam','houvesse','houvéssemos','houvessem','houver','houvermos','houverem','houverei','houverá','houveremos','houverão','houveria','houveríamos','houveriam','sou','somos','são','era','éramos','eram','fui','foi','fomos','foram','fora','fôramos','seja','sejamos','sejam','fosse','fôssemos','fossem','for','formos','forem','serei','será','seremos','serão','seria','seríamos','seriam','tenho','tem','temos','tém','tinha','tínhamos','tinham','tive','teve','tivemos','tiveram','tivera','tivéramos','tenha','tenhamos','tenham','tivesse','tivéssemos','tivessem','tiver','tivermos','tiverem','terei','terá','teremos','terão','teria','teríamos','teriam', 'contudo','pois','então','logo','assim','além','ainda','também','outra','outro','outras','outros','cada','certo','certos','certa','certas','qualquer','quaisquer','muito','muita','muitos','muitas','pouco','pouca','poucos','poucas','todo','toda','todos','todas','tal','tais', 'porém', 'pra','nbsp', 'nesse', 'disso'
            ];

            $args = [
                'post_type' => [ 'post', 'page' ],
                'post_status' => 'publish',
                'posts_per_page' => -1,
                'fields' => 'ids',
            ];
            $query = new WP_Query( $args );
            $all_text = '';
            if ( $query->have_posts() ) {
                foreach ( $query->posts as $post_id ) {
                    $post = get_post( $post_id );
                    $all_text .= ' ' . $post->post_title . ' ' . $post->post_content;
                }
            }

            $all_text = mb_strtolower( wp_strip_all_tags( $all_text ), 'UTF-8' );
            $all_text = preg_replace( '/[\d\W_]+/u', ' ', $all_text );
            $words_array = preg_split( '/\s+/u', $all_text, -1, PREG_SPLIT_NO_EMPTY );

            $words = [];
            foreach ( $words_array as $word ) {
                if ( strlen( $word ) < 3 ) continue;
                if ( in_array( $word, $stopwords ) ) continue;
                if ( ! isset( $words[ $word ] ) ) {
                    $words[ $word ] = 0;
                }
                $words[ $word ]++;
            }

            arsort( $words );
            $words = array_slice( $words, 0, 50, true );

            if ( file_exists( __DIR__ . '/WordCloudSVG.php' ) ) {
                require_once __DIR__ . '/WordCloudSVG.php';
                $cloud = new \Eufelipemateus\Trends\WordCloudSVG( 800, 400 );
                $cloud->setWords( $words );
                return '<div style="overflow-x:auto; display:flex; justify-content:center;">' . $cloud->generate() . '</div>';
            } else {
                return '<p>Erro: WordCloudSVG.php não encontrado.</p>';
            }
        }
    }
    Trends_Plugin::init();
}
