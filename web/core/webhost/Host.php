<?php
namespace OpenPanel\core\webhost;
use OpenPanel\core\db\Database;
use OpenPanel\core\db\Model;
use OpenPanel\core\logging\Logger;

class Host extends Model {
  public int $id;
  public string $hostname;
  public int $port;
  public int $portssl;

  static string $table = "hosts";
  protected static array $fields = ["id", "hostname", "port", "portssl"];

  public function getEditUrl() {
    return "/hosting/edit?id=$this->id";
  }

  public function getDeleteUrl() {
    return "/hosting/delete?id=$this->id";
  }

  public function setup() {
    
  }
}