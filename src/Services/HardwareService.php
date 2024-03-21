<?php

namespace Taecontrol\Larvis\Services;

use Taecontrol\Larvis\Exceptions\CpuHealthException;
use Taecontrol\Larvis\Exceptions\DiskHealthException;
use Taecontrol\Larvis\Exceptions\MemoryHealthException;

class HardwareService
{
    public function getHardwareData(): array
    {
        $cpuLoad = $this->getCpuLoadUsage();
        $memory = $this->getMemoryUsage();
        $disk = $this->getDiskUsage();

        $HardwareData = [
            'cpuLoad' => $cpuLoad,
            'memory' => $memory,
            'disk' => $disk,
        ];

        return $HardwareData;
    }

    public function getDiskUsage(): array
    {
        try {
            $freeSpace = false;
            $totalSpace = false;

            if (function_exists('disk_free_space') && function_exists('disk_total_space')) {
                $freeSpace = round((disk_free_space('/') / pow(1024, 3)), 1);
                $totalSpace = round((disk_total_space('/') / pow(1024, 3)), 1);
            }

            $result = [
                'freeSpace' => $freeSpace,
                'totalSpace' => $totalSpace,
            ];

            return $result;
        } catch (DiskHealthException $e) {
            throw $e->make();
        }
    }

    public function getMemoryUsage(): float
    {
        try {
            if (function_exists('exec')) {
                $memory = shell_exec(" free | grep Mem | awk '{print $3/$2 * 100}' ");
                $result = round((float) $memory);
            }

            return $result;
        } catch(MemoryHealthException $e) {
            throw $e->make();
        }
    }

    public function getCpuLoadUsage(): float
    {
        try {
            $result = false;

            if (function_exists('sys_getloadavg') && function_exists('exec')) {
                // loadavg 1min, 5min, 15min
                $result = sys_getloadavg();
                $num_cores = floatval(shell_exec("cat /proc/cpuinfo | grep processor | wc -l"));
                if ($num_cores == 0) {
                    $num_cores = 1;
                }
                $cpu_5min = round($result[1] / $num_cores * 100.0, 2);

                return $cpu_5min;
            }
        } catch(CpuHealthException $e) {
            throw $e->make();
        }
    }

}
