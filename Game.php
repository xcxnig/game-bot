<?

include 'Medoo.php';
use Medoo\Medoo;

class Game
{

	public $database;
	public $tgid;
	public $key;

	function __construct( $config ){
		$this->database = new medoo($config);
	}

	public function register( ){

		$name = $database->get("user","user_name", [
            "telegram_id" => $this->tgid
        ]);
	    return $name?:0;
        
	}

	public function open(){
		
		$opendata = $this->getlottery(1)[0];
		$opencode = explode(",",$opendata["code"])[0];

		$lottery = $this->$database->select("game", [
			"id",
		    "user",
		    "tgname",
		    "project",
		    "type",
		    "result",
		    "data"
        ],[
			"expect" => $opendata["issue"],
			"result" => "未开奖"
		]);

		if(isset($lottery)){
			if ($opencode%2 == 0)
			    $result1 = '双';
			else
			    $result1 = '单';
			
			if ($opencode >= 5) {
			    $result2 = '大';
			else
			    $result2 = '小';
			//流量输掉的奖池
			$data1 = $database->sum("game","data", [
			    "expect" => $expect,
			    "project[!]" => [$result1,$result2],
			    "type" => '流量',
			    "result" => '未开奖'
			]);
			$data2 = $database->sum("game","data", [
			    "expect" => $expect,
			    "project" => [$result1,$result2],
			    "type" => '流量',
			    "result" => '未开奖'
			]);
			//余额输掉的奖池
			$data3 = $database->sum("game","data", [
			    "expect" => $expect,
			    "project[!]" => [$result1,$result2],
			    "type" => '余额',
			    "result" => '未开奖'
			]);
			$data4 = $database->sum("game","data", [
			    "expect" => $expect,
			    "project" => [$result1,$result2],
			    "type" => '余额',
			    "result" => '未开奖'
			]);

			foreach ($lottery as $v) {
			    if ($v['project'] == $result1 OR $v['project'] == $result2) {

			        $result = '已中奖';

			        if ($v['type'] == '余额') {
			            $rrr = $v['data'] + round(($v['data']/$data4)*$data3,2);

			            $this->database->update("user", [
			                "money[+]" => $rrr
			            ], [
			                "telegram_id" => $v['user']
			            ]);

			        } elseif ($v['type'] == '流量') {

			            $rrr1 = ceil(($v['data']/$data2)*$data1);
			            $rrr = ($v['data'] + $rrr1)*1024*1024;

			            $this->database->update("user", [
			                "transfer_enable[+]" => $rrr
			            ], [
			                "telegram_id" => $v['user']
			            ]);
			        }
			        $text = $text."\n@".$v['tguser'].' 第'.$expect.'期彩票已中奖，获得'.$v['type'].' '.$rrr1;

			    } else {
			        $text = $text."\n@".$v['tguser'].' 第'.$expect.'期彩票未中奖';
			        $result = '未中奖';
			    }

			    $this->database->update("game", [
			        "result" => $result
			    ], [
			        "id" => $v['id']
			    ]);

			}
		}else{
			$text = false;
		}
		return $text;
	}


	public function getlottery( $number ){

		$json = file_get_contents("http://vip.manycai.com/".$this->key."/tcpl5-".$number.".json");
		return json_decode($json,true);
	
	}

	public function getopendata( ){

		$lottery = $this->getlottery(5);
		$info = "开奖记录：";

		foreach ($lottery as $v) {
            $info .= "\n第".$v['issue'].'期：'.$v['code'];
        }

		return $info;
	}

	public function getissue(){

		$issue_old = $this->getlottery(1)[0]["issue"];
		
		$issue = $issue_old % 1000;
		if( $issue == 358)
			$issue = (date("Y")+1)."001";
		else
			$issue = $issue_old + 1;

		return $issue;
	}


	public function dataswap( $data ){

		$data = $data/1024/1024;
		if ($data > 1048576)
			return sprintf("%.2f",$data/1048576).' TB';
		elseif ($data > 1024)
			return sprintf("%.2f",$data/1024).' GB';
		else
			return $data.' MB';
	}


	public function getinfo(){

		$info = "";
		$res = $this->getuser();

        $info = "账户信息：\n流量剩余：".$this->dataswap($res['transfer_enable']-$res['u']-$res['d'])." MB\n余额剩余：".$res['money']." 元\n\n下注记录：";

        $lottery = $this->$database->select("game", [
            "expect",
            "project",
            "type",
            "data",
            "result"
        ], [
            "user" => $this->tgid,
            "ORDER" => ["id"=>"DESC"],
            "LIMIT" => 10
        ]);

		foreach ($lottery as $v) {
			$info .= "\n\n第".$v['expect']."期：\n投注：[".$v['type']."][".$v['project']."][".$v['data']."]\n结果：".$v['result'];
		}

        return $info;
	}

	public function getuser(){

		$res = $this->database->get("user", [
            "transfer_enable",
            "u",
            "d",
            "money"
        ] , [
            "telegram_id" => $this->tgid
        ]);

        return $res;

	}

	public function setinfo($data,$type,$h_type){

		if($h_type == "L") 
			$a = "transfer_enable";
		else 
			$a = "money";

		$this->database->update("user", [
            $a.$type => $data
        ], [
            "telegram_id" => $this->tgid
        ]);

	}

	public function setlottery( $project,$type,$data ){

		$expect = $this->getissue();
		$this->$database->insert("game", [
            "user" => $this->tgid,
            "project" => $project,
            "type" => $type,
            "data" => $data,
            "result" => '未开奖',
            "expect" => $expect
        ]);

        return $expect;
	}

}
?>