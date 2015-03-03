<?php
class Params{
	function setScheduleGetEventsByTarget($uid, $start, $end){
		$this->params = '
			<parameters start="' . $start . '" end="' . $end . '">
				<user xmlns="" id="'. $uid .'"></user>
			</parameters>';
	}

	function getScheduleGetEventsByTarget(){
		return $this->params;
	}
}
?>
