<?php

/**
 * @description popular development tools
 * @author by ricolau<ricolau#foxmail.com>(replace # with @)
 * @version 2018-01-22
 *
*/

define("PHP_TOOLS_PDO_MODE", true);
if(!class_exists('PDO')){
	
	throw new Exception('class pdo does not exist!');
}



class mysqld{
	protected $_con = null;

	protected static function _checkArgs(&$args){
		if(!isset($args['host'])){
			$args['host'] = 'localhost';
		}
		if(!isset($args['port'])){
			$args['port'] = 3306;
		}
		if(!isset($args['charset'])){
			$args['charset'] = 'utf8';
		}
		return true;
	}
	/**
		* $args = array('host'=>, 'port'=>, 'user'=>, 'pwd'=>, 'db'=>, 'charset'=>);
		*  $args = 'mysql -hlocalhost -P3306 -uroot -ppasswod dbname';
		*/
	public function __construct($args){
		if(!is_array($args)){
			$args = $this->_parseArgs($args);
		}
		if(!self::_checkArgs($args)){
			return false;
		}
		if(!isset($this->_con)){
			$this->_connect($args);
		}
		return $this;
	}
	protected function _parseArgs($args){
		if(!is_string($args)){
			return false;
		}
		$data = explode(' ', $args);
		$c = array();
		foreach($data as $v){
			$pre = substr($v, 0, 2);
			$v = trim($v);
			if($v =='mysql'){
				continue;
			}
			if($pre == '-h'){
				$c['host'] = substr($v, 2);
			}elseif( $pre== '-u'){
				$c['user'] = substr($v, 2);
			}elseif($pre == '-P'){
				$c['port'] = substr($v, 2);
			}elseif($pre == '-p'){
				$c['pwd'] = substr($v, 2);
			}else{
				$c['db'] = $v;
			}
		}
		return $c;
	}

	protected function _connect($args){	
		
		if($this->_con){
			return $this->_con;
		}
		if(!$args['host']){
			$args['host'] = 'localhost';
		}
		if(!$args['port']){
			$args['port'] = 3306;
		}

		$dsn = 'mysql:dbname=' . $args['db'] . ';host=' . $args['host'] . ';port=' . $args['port'];
		try{
			$this->_con = new PDO($dsn, $args['user'], $args['pwd'], $args['options']);
			if(!isset($args['options'][PDO::ATTR_EMULATE_PREPARES])) {
				//tell the mysql pdo do not stringfy field values!~!
				$this->_con->setAttribute(PDO::ATTR_EMULATE_PREPARES, FALSE);
			}
			
			if(isset($args['charset']) && $args['charset']) {
				$this->_con->query('SET NAMES ' . $args['charset']);
			}
		}catch(Exception $e){
			throw $e;
		}
		
		return $this->_con;
	}

	public function fetch($sql, $fetchone = false){

		$sth = $this->_con->prepare($sql);
		if($sth===false){
			throw new Exception('prepare failed for '.__METHOD__.' :'.$sql);
		}
		$res = $sth->execute();
		if ($res) {
			$collect = $sth->fetchAll(PDO::FETCH_ASSOC);
		}else{
			throw new Exception('execute failed for '.__METHOD__.' :'.$sql);
		}

		if($fetchone && is_array($collect)){
			$collect  = array_shift($collect);
		}
		return $collect;
	}

	public function query($sql){
		if(!$this->_con){
			return false;
		}
		$res = $this->_con->query($sql);
		return $res;
	}
	public function update($data, $where, $tablename){
		if(empty($data) || empty($where) || empty($tablename)){
			return false;
		}
		$fields = $values = array();
		foreach($data as $k=>$v){
			$v= addslashes($v);
			$fields[] = $k . '= ? ';
			$values[] = $v;
			$vstr[] = '`'.$k.'`='." '$v' ";
		}
	

		$wheresql = $where ? ' WHERE '. $where : '';

		$sql = "UPDATE $tablename SET ".implode(',', $vstr) ." ".$wheresql;
		$sth = $this->_con->prepare($sql);
		if($sth===false){
			throw new Exception('prepare failed for '.__METHOD__.' :'.$sql);
		}

		$res = $sth->execute();
		if ($res) {
			$res = $sth->rowCount();
		}else{
			throw new Exception('execute failed for '.__METHOD__.' :'.$sql);
		}
		return $res;

	}

	public function insert($data, $tablename){
		$fields = array_keys($data);
		$values = array_values($data);
		$values = array_map('addslashes',$values);

		$insteads = array_fill(0, count($values), '?');

        $sql = 'INSERT INTO ' . $tablename . '(`' . implode('`, `', $fields) . '`) VALUE(\'' . implode('\', \'', $values) . '\')';
		$sth = $this->_con->prepare($sql);
		if($sth===false){
			throw new Exception('prepare failed for '.__METHOD__.' :'.$sql);
		}
		$res = $sth->execute();

	
		if ($res !== false) {
			$res = $this->_con->lastInsertId();
		}else{
			throw new Exception('execute failed for '.__METHOD__.' :'.$sql);
		}
		return $res;
	}
	public function replace($data, $tablename){
		$fields = array_keys($data);
		$values = array_map('addslashes',array_values($data));

		$insteads = array_fill(0, count($values), '?');

        $sql = 'REPLACE INTO ' . $tablename . '(`' . implode('`, `', $fields) . '`) VALUE(\'' . implode('\', \'', $values) . '\')';
		$sth = $this->_con->prepare($sql);
		if($sth===false){
			throw new Exception('prepare failed for '.__METHOD__.' :'.$sql);
		}
		$res = $sth->execute();
		

		if ($res !== false) {
			$res = $this->_con->lastInsertId();
		}else{
			throw new Exception('execute failed for '.__METHOD__.' :'.$sql);
		}
		return $res;
	}

	public function error(){
		return $this->_con->errorInfo();
	}

}
