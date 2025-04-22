<?php

if (! function_exists('greeting')) {
    function greeting($name)
    {
        return "Hello, $name!";
    }
}

if (! function_exists('get_uuid')) {
    function get_uuid($prefix = '')
    {
        $chars = md5(uniqid(mt_rand(), true));
        $uuid = substr($chars, 0, 8) . '-';
        $uuid .= substr($chars, 8, 4) . '-';
        $uuid .= substr($chars, 12, 4) . '-';
        $uuid .= substr($chars, 16, 4) . '-';
        $uuid .= substr($chars, 20, 12);
        return $prefix . $uuid;
    }
}

if (! function_exists('linksPage')) {
    function linksPage($page, $i)
    {
        try {
            if (!empty($page)) {
                if (empty($page)) {
                    $q = 1;
                } else {
                    $q = $page - 1;
                }

                if (($q + 2) < $i) {
                    $qh = $q + 2;
                } else {
                    $qh = $i;
                }

                $report = array();
                if ($page > 2) {
                    $report[] = array(
                        'link' => 1,
                        'label' => '‹ First',
                        'active' => false
                    );
                }
                if ($page > 1) {
                    $report[] = array(
                        'link' => ($page - 1),
                        'label' => '<',
                        'active' => false
                    );
                }
                for ($j = ($q == 0 ? 1 : $q); $j <= $qh; $j++) {
                    $report[] = array(
                        'link' => $j,
                        'label' => $j,
                        'active' => ($page == $j ? true : false)
                    );
                }
                if ($page < $qh) {
                    $report[] = array(
                        'link' => ($page + 1),
                        'label' => '>',
                        'active' => false
                    );
                }
                if ($i > $qh) {
                    $report[] = array(
                        'link' => $i,
                        'label' => 'Last ›',
                        'active' => false
                    );
                }
            }

            return $report;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }
}

if (! function_exists('simpleLink')) {
    function simpleLink($page, $i)
    {
        try {
            if (!empty($page)) {
                if ($i <= 1) {
                    $dis = 'disabled';
                } else if ($page == $i) {
                    $dis = 'disabled';
                } else {
                    $dis = '';
                }

                $report = array();
                $report[] = array(
                    'link' => ($page == 1 ? '1' : $page - 1),
                    'label' => 'fa fa-fw fa-arrow-left',
                    'disabled' => ($page == 1 ? 'disabled' : ''),
                );
                $report[] = array(
                    'link' => ($page == $i ? $i : $page + 1),
                    'label' => 'fa fa-fw fa-arrow-right',
                    'disabled' => $dis,
                );
            }

            return $report;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }
}
