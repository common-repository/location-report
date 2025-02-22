<?php
namespace libKML;

/**
 *  TimeStamp class
 */

class TimeStamp extends TimePrimitive {
  
  private $when;
  
  public function __toString() {
    $parent_string = parent::__toString();
    
    $output = array();
    if (isset($this->when)) {
      $output[] = sprintf("<TimeStamp%s>",
                          isset($this->id)?sprintf(" id=\"%s\"", $this->id):"");
      $output[] = $parent_string;
      
      $output[] = sprintf("\t<when>%s</when>", htmlentities($this->when));
      
      $output[] = "</TimeStamp>";
    }
    
    return implode("\n", $output);
  }
  
  public function getWhen() {
    return $this->when;
  }
  
  public function setWhen($when) {
    $this->when = $when;
  }
  
}
?>
