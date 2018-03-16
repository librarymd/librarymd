<?php

/*
  require ("smtp.inc.php");
  
  $smtp=new SMTPMAIL;
  
  $from="HARISH<harishc@templatearena.com>";
  $to="harish<harishc@templatearena.com>";
  $cc="Tapan<tapank@templatearena.com>;manoj<manojk@templatearena.com>";
  $subject="welcome";
  $body="This is test mail.don't reply it.";

  if(!$smtp->send_smtp_mail($to,$subject,$body,$from))
	echo "Error in sending mail!<BR>Error: ".$smtp->error;
  else
	echo "Mail sent succesfully!";
*/

	class SMTPMAIL
	{
		var $host="";
		var $port=25;
		var $error;
		var $state;
		var $con=null;
		var $greets="";
		
		function SMTPMAIL()
		{
			$this->host=ini_get("SMTP");
			$this->port=ini_get("smtp_port");
			$this->state="DISCONNECTED";
		}
		function error()
		{
			return $this->error;
		}
		function connect()
		{
            if($this->state == "CONNECTED") return true;
            
			if($this->state!="DISCONNECTED")
			{
				$this->error="Error : connection already open.";
				return false;
			}
            
			$this->con=@fsockopen($this->host,$this->port,$errno,$errstr);
			if(!$this->con)
			{
				$this->error="Error($errno):$errstr";
				return false;
			}
			$this->state="CONNECTED";
			$this->greets=$this->get_line();
            $this->put_line("HELO localhost");
            $this->get_line();
			return true;
		}
		function send_smtp_mail($to,$subject,$data,$from='localhost')
		{
			$ret=$this->connect();
			if(!$ret) return $ret;
			$this->put_line("MAIL FROM: $from");
			$response=$this->get_line();
			if(intval(strtok($response," "))!=250)
			{
				$this->error=strtok($response,"\r\n");
				return false;
			}
			$to_err=preg_split("/[,;]/",$to);
			foreach($to_err as $mailto)
			{
				$this->put_line("RCPT TO: $mailto");
				$response=$this->get_line();
				if(intval(strtok($response," "))!=250)
				{
					$this->error=strtok($response,"\r\n");
					return false;
				}
			}
			$this->put_line("DATA");
			$response=$this->get_line();
			if(intval(strtok($response," "))!=354)
			{
				$this->error=strtok($response,"\r\n");
				return false;
			}
			$this->put_line("To: $to");
			$this->put_line("Subject: $subject");
			$this->put_line($data);
			$this->put_line(".");
			$response=$this->get_line();
			if(intval(strtok($response," "))!=250)
			{
				$this->error=strtok($response,"\r\n");
				return false;
			}
			$this->close();
			return true;
		}
		// This function is used to get response line from server
		function get_line()
		{
            $line = '';
			while(!feof($this->con))
			{
				$line.=fgets($this->con);
				if(strlen($line)>=2 && substr($line,-2)=="\r\n")
					return(substr($line,0,-2));
			}
		}
		////This functiuon is to retrive the full response message from server

		////This functiuon is to send the command to server
		function put_line($msg="")
		{
			return @fputs($this->con,"$msg\r\n");
		}
		
		function close()		
		{
			@fclose($this->con);
			$this->con=null;
			$this->state="DISCONNECTED";
		}
	}

?>