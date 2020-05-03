/* Representing the hex string as integers */
    private function hexStringToIntegers($msg)
    {
        $hexStringLength = strlen($msg);
        $hex = str_split($msg, 2);
        $length = count($hex);
        $v = [];
        for ($i = 0; $i < $length; $i++) {
            array_push($v, hexdec($hex[$i]));
        }

        if ($hexStringLength <= 6) {
            return $this->shortPayloadDecoder($msg);
        } else if ($hexStringLength > 6) {
            return $this->longPayloadDecoder($msg, $v);
        }

        return null;
    }

    private function shortPayloadDecoder($msg)
    {
        $out = [];
        $out['bat'] = substr($msg, -2);
        if ($msg[1] == 0) {
            $out['temp'] = substr($msg, 2, 2) / 10;
        } else {
            $out['temp'] = substr($msg, 1, 3) / 10;
        }
        $out['temp'] = $msg[0] == 0 ? $out['temp'] : $out['temp'] * -1; //prefix;

        return $out;
    }

    private function longPayloadDecoder($msg, $d)
    {
        $out = [];
        $lat = (($d[0] << 16) | ($d[1] << 8) | ($d[2]));
        $lon = (($d[3] << 16) | ($d[4] << 8) | ($d[5]));

        $out['alt'] = (($d[6] << 8) | ($d[7]));
        $out['bat'] = substr($msg, -2);

        if ($msg[17] == 0) {
            $out['temp'] = substr($msg, 18, 2) / 10;
        } else {
            $out['temp'] = substr($msg, 17, 3) / 10;
        }
        $out['temp'] = $msg[16] == 0 ? $out['temp'] : $out['temp'] * -1; //prefix;

        if ($lat == 0 || $lon == 0) {
            return false;
        }

        if (($lat & 1 << 23) != 0) {
            $lat = -(1 << 24) + $lat;
        }

        $lat /= 1 << 23;
        $lat *= 90;

        if (($lon & 1 << 23) != 0) {
            $lon = -(1 << 24) + $lon;
        }

        $lon /= 1 << 23;
        $lon *= 180;

        $out['lat'] = $lat;
        $out['lon'] = $lon;

        return $out;
    }