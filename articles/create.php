<?php
    $variables = file_get_contents("php://input");
        
    //xmlで返す値、OK or errorで
    $status = "";
    $variables_array = [];
    
    if( !is_string( $variables ) ){ $status = "error"; }
    if( trim( $variables ) === "" ){ $status = "error";}
    
    $variables_arr = explode('&', $variables);
    
    foreach( $variables_arr as $variable){
        
        if( strpos( $variable, "=") !== false){
            $var_tmp = explode( "=" , $variable );
            $keys = urldecode( $var_tmp[0] );
            $val = urldecode( $var_tmp[1] );
        }
        
        $left_sym = strpos( $keys , "[" );
        $want = substr( $keys , $left_sym+1 , -1);
        
        $variables_array += array( $want => $val );
    }
    
    //更新日用
    date_default_timezone_set('Asia/Tokyo');
    /*  check 連載記事:常に-1
     *  title　記事タイトル
     *  author　投稿者
     *  source_author　講演者
     *  body　　記事本文
     *  category_id　記事カテゴリ
     *  category_id2　記事副カテゴリ
     *  lecture_date 公演日
     *  updated_at 更新日
     */
    $create_body = htmlspecialchars( $variables_array["body"] );
    $create_title = htmlspecialchars( $variables_array["title"] );
    $create_author = htmlspecialchars( $variables_array["author"] );
    $create_source_author = htmlspecialchars( $variables_array["source_author"] );
    $create_category_id = htmlspecialchars( $variables_array["category_id"] );
    $create_category_id2 = htmlspecialchars( $variables_array["category_id2"] );
    $create_lecture_date = htmlspecialchars( $variables_array["lecture_date"] );
    $create_updated_at = htmlspecialchars( date('Y年m月d日') );
    
    
    //全角/半角スペースを除去して、既存ユーザと一致するか
    $tmp_author = $create_author;
    $tmp_author = preg_replace('/(\s|　)/','', $tmp_author);
    $author_id = "";
    
    //投稿者が既存ユーザなら、xmlにユーザIDの保存をする
    $profilesfile = "../receptions_file/profiles.xml";
    if( $xml = simplexml_load_file( $profilesfile ) ){
        foreach( $xml as $profile){
            if(  (string)$profile->name === $tmp_author ){
                $author_id = $profile->id;
            }
        }
    }else{ echo "cant read file:". $profilesfile ;}

    
    $rootNode = new SimpleXMLElement( "<?xml version='1.0' encoding='utf-8' standalone='yes'?><articles></articles>" );
    
    $articleNode = $rootNode->addChild('article');
    $articleNode -> addchild( "title" , $create_title);
    $articleNode -> addchild( "author" , $create_author);
    $articleNode -> addchild( "author_id" , $author_id);
    $articleNode -> addchild( "source_author" , $create_source_author);
    $articleNode -> addchild( "category_id" , $create_category_id );
    $articleNode -> addchild( "category_id2" , $create_category_id2 );
    $articleNode -> addchild( "lecture_date" , $create_lecture_date );
    $articleNode -> addchild( "updated_at" , $create_updated_at );
    $articleNode -> addchild( "body" , $create_body);
    
    $dom = new DOMDocument('1.0');
    $dom -> LoadXML( $rootNode->asXML() );
    $dom -> formatOutput = true;
    
    if( $dom -> save("../receptions_file/article.xml") ){
        exec("php ../makefile.php" , $exec_error);
        
        if( !empty($exec_error) ){
            $log_file =  "../logs/log".date("mdGis").".txt";            
            file_put_contents($log_file, $exec_error);
            $status = "error";
        }else{
            $exec_error = ""; 
            $status = "OK";
        }        
    }
    
    header("Content-Type: text/xml");
?>
<status><?php echo $status; ?></status>