<?PHP
    class GoogleVoice
    {
        public $username;
        public $password;

        private $lastURL;

        public function __construct($username, $password)
        {
            $this->username = $username;
            $this->password = $password;
        }

        // Login to Google Voice
        public function login()
        {
            $html = $this->curl('http://www.google.com/voice/m');

            $action = $this->match('!<form.*?action="(.*?)"!ms', $html, 1);

            preg_match_all('!<input.*?type="hidden".*?name="(.*?)".*?value="(.*?)"!ms', $html, $hidden);

            $post = "Email={$this->username}&Passwd={$this->password}";
            for($i = 0; $i < count($hidden[0]); $i++)
                $post .= '&' . $hidden[1][$i] . '=' . urlencode($hidden[2][$i]);

            $html = $this->curl($action, $this->lastURL, $post);

            return $html;
        }
				// Send a $text to $num. $num is 10 digit US phone number
				public function sms($text,$num) {

					$num = preg_replace('/[^0-9]/', '', $num);
					$html = $this->login();

					$crumb = urlencode($this->match('!<input.*?name="_rnr_se".*?value="(.*?)"!ms', $html, 1));

					$post = "phoneNumber=$num&text=".urlencode($text)."&_rnr_se=$crumb";

					$html=$this->curl("https://www.google.com/voice/sms/send",$this->lastURL,$post);
					return $html;

				}
        // Connect $you to $them. Takes two 10 digit US phone numbers.
        public function call($you, $them)
        {
            $you = preg_replace('/[^0-9]/', '', $you);
            $them = preg_replace('/[^0-9]/', '', $them);

            $html = $this->login();

            $crumb = urlencode($this->match('!<input.*?name="_rnr_se".*?value="(.*?)"!ms', $html, 1));

            $post = "_rnr_se=$crumb&number=$them&call=Call";
            $html = $this->curl("https://www.google.com/voice/m/callsms", $this->lastURL, $post);

            preg_match_all('!<input.*?type="hidden".*?name="(.*?)".*?value="(.*?)"!ms', $html, $hidden);
            $post = '';
            for($i = 0; $i < count($hidden[0]); $i++)
                $post .= '&' . $hidden[1][$i] . '=' . urlencode($hidden[2][$i]);
            $post .= "&phone=+1$you&Call=";

            $html = $this->curl("https://www.google.com/voice/m/sendcall", $this->lastURL, $post);
        }

        public function sms($you, $them,$smtxt)
        {
            $you = preg_replace('/[^0-9]/', '', $you);
            $them = preg_replace('/[^0-9]/', '', $them);

            $html = $this->login();

            $crumb = urlencode($this->match('!<input.*?name="_rnr_se".*?value="(.*?)"!ms', $html, 1));

            $post = "_rnr_se=$crumb&number=$them&smstext=$smtxt&submit=Send";
            $html = $this->curl("https://www.google.com/voice/m/sendsms", $this->lastURL, $post);

            preg_match_all('!<input.*?type="hidden".*?name="(.*?)".*?value="(.*?)"!ms', $html, $hidden);
            $post = '';
            for($i = 0; $i < count($hidden[0]); $i++)
                $post .= '&' . $hidden[1][$i] . '=' . urlencode($hidden[2][$i]);
            $post .= "&submit=";

            $html = $this->curl("https://www.google.com/voice/m/sendcall", $this->lastURL, $post);
        }

        private function curl($url, $referer = null, $post = null, $return_header = false)
        {
            static $tmpfile;

            if(!isset($tmpfile) || ($tmpfile == '')) $tmpfile = tempnam('/tmp', 'FOO');

            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_COOKIEFILE, $tmpfile);
            curl_setopt($ch, CURLOPT_COOKIEJAR, $tmpfile);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (iPhone; U; CPU iPhone OS 2_2_1 like Mac OS X; en-us) AppleWebKit/525.18.1 (KHTML, like Gecko) Version/3.1.1 Mobile/5H11 Safari/525.20");
            if($referer) curl_setopt($ch, CURLOPT_REFERER, $referer);

            if(!is_null($post))
            {
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
            }

            if($return_header)
            {
                curl_setopt($ch, CURLOPT_HEADER, 1);
                $html        = curl_exec($ch);
                $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
                $this->lastURL = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
                return substr($html, 0, $header_size);
            }
            else
            {
                $html = curl_exec($ch);
                $this->lastURL = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
                return $html;
            }
        }

        private function match($regex, $str, $i = 0)
        {
            return preg_match($regex, $str, $match) == 1 ? $match[$i] : false;
        }
    }

    // Example Usge:
    $gv = new GoogleVoice('username@gmail.com', 'password');
    $gv->call('yournumber', 'their number');

    $gb->sms('hello world','somenumber');
