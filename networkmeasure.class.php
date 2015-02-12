<?php
/**
 * Network statistics
 */
namespace network;

/**
 * Class MeasureNetwork
 * @package network
 */
class MeasureNetwork
{

    protected $network_file = "/proc/net/dev";
    protected $NetworkInterfaces = [];
    protected $InterfaceStats = [];
    protected $NetworkStatistics = [];
    protected $network_data = [];
    protected $u_remove = [];
    protected $network_temp = [];
    protected $DownRate = null;
    protected $UpRate = null;
    protected $interface = null;
    protected $interface_data = [];

    /**
     * Parse file as array
     * @return $this
     */
    protected function parse_file()
    {
        $this->network_data = file($this->network_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        $this->u_remove[] = array_shift($this->network_data);
        $this->u_remove[] = array_shift($this->network_data);
        return $this;
    }

    /**
     * Parse file array as data
     * @return $this
     */
    protected function parse_args()
    {

        foreach ($this->network_data as $interface_id => $interface_data) {
            $this->NetworkInterfaces[$interface_id] = $this->__trim(explode(':', $interface_data)[0], true);

            $inline = $this->__trim(explode(':', $interface_data)[1]);
            $in_array = explode(' ', $inline);
            $this->InterfaceStats[$this->NetworkInterfaces[$interface_id]] = $in_array;

        }

        return $this;
    }

    /**
     * Remove whitespaces and spaces
     * @param $string
     * @param bool $withAll
     * @return mixed
     */
    protected function __trim($string, $withAll = false)
    {
        if ($withAll)
            return str_replace(' ', '', $string);
        else
            return preg_replace('/\s+/', ' ', $string);

    }

    /**
     * Calculate network bitrate
     * @return mixed
     */
    protected function __final()
    {
        $this->network_temp[] = $this->parse_file()->parse_args()->__info();
        sleep(1);
        $this->network_temp[] = $this->parse_file()->parse_args()->__info();

        $this->DownRate = ($this->network_temp[1][$this->interface][1] - $this->network_temp[0][$this->interface][1]) * 8;
        $this->UpRate = ($this->network_temp[1][$this->interface][9] - $this->network_temp[0][$this->interface][9]) * 8;

        $traffic['down'] = round($this->DownRate / 1024);
        $traffic['up'] = round($this->UpRate / 1024);

        return $traffic;
    }

    /**
     * Set interface
     * @param string $interface
     * @return $this
     */
    public function WithInterface($interface = "eth0")
    {
        $this->interface = $interface;
        return $this;
    }

    /**
     * Get InterfaceStats
     * @return array
     */
    protected function __info()
    {
        return $this->InterfaceStats;
    }

    /**
     * Measure traffic
     * @return mixed
     */
    public function Measure()
    {
        $data['bitrate'] = $this->__final();
        $data['percent'] = number_format(($data['bitrate']['up'] + $data['bitrate']['down']) / (1024 * 1024) * 100, 2);
        return $data;
    }


}


print_r((new MeasureNetwork())->WithInterface('em1')->Measure());
