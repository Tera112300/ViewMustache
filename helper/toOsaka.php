<?php
    // 大阪弁に変換する
    function toOsaka_func( $value = null ) {
        $tokyo = array("ありがとう","です。","ます。",  "ました。"   );
        $osaka = array("おおきに",  "やで。","まっせ。","ましてん。" );
        return nl2br( str_replace( $tokyo, $osaka, $value ) );
    }
?>