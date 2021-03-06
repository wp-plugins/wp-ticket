<?php
/**
 * Date Functions
 *
 * @package     EMD
 * @copyright   Copyright (c) 2014,  Emarket Design
 * @since       1.0
 */
if (!defined('ABSPATH')) exit;
/**
 * Gets date in specific format
 *
 * @since WPAS 4.0
 * @param array $myfield_opt field info
 * @param string $meta_value value of attribute
 * @param bool $reverse where to display
 * @return string $meta_value attr with new format
 */
function emd_translate_date_format($myfield_opt, $meta_value, $reverse = 0) {
	$emd_date_format_translation = array(
		'd' => 'j',
		'dd' => 'd',
		'oo' => 'z',
		'D' => 'D',
		'DD' => 'l',
		'm' => 'n',
		'mm' => 'm',
		'M' => 'M',
		'MM' => 'F',
		'y' => 'y',
		'yy' => 'Y'
	);
	$emd_time_format_translation = array(
		'H' => 'H',
		'HH' => 'H',
		'h' => 'H',
		'hh' => 'H',
		'mm' => 'i',
		'ss' => 's',
		'l' => 'u',
		'tt' => 'a',
		'TT' => 'A'
	);
	if (isset($myfield_opt['type']) && $meta_value != '' && !empty($myfield_opt['dformat'])) {
		$type = $myfield_opt['type'];
		$format = $myfield_opt['dformat'];
		$dformat = isset($format['dateFormat']) ? $format['dateFormat'] : '';
		$tformat = isset($format['timeFormat']) ? $format['timeFormat'] : '';
		switch ($type) {
			case 'date':
				$new_format = strtr($dformat, $emd_date_format_translation);
				$data_format = 'Y-m-d';
			break;
			case 'datetime':
				$new_format = strtr($dformat, $emd_date_format_translation) . " " . strtr($tformat, $emd_time_format_translation);
				if ($tformat == 'hh:mm:ss') {
					$data_format = 'Y-m-d H:i:s';
				} else {
					$data_format = 'Y-m-d H:i';
				}
			break;
			case 'time':
				$new_format = strtr($tformat, $emd_time_format_translation);
				if ($tformat == 'hh:mm:ss') {
					$data_format = 'H:i:s';
				} else {
					$data_format = 'H:i';
				}
			break;
			default:
				return $meta_value;
		}
		if ($reverse == 1) {
			if(DateTime::createFromFormat($data_format, $meta_value)){
				return DateTime::createFromFormat($data_format, $meta_value)->format($new_format);
			}
		} else {
			if(DateTime::createFromFormat($new_format, $meta_value)){
				return DateTime::createFromFormat($new_format, $meta_value)->format($data_format);
			}
		}
	}
	return $meta_value;
}
