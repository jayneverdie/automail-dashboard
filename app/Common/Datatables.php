<?php

namespace App\Common;

class Datatables
{
	public function filter($filterData, $filter = null) {
		$search = [];

		if (count($filterData) > 0) {
			foreach ($filterData['columns'] as $s) {
				if ($s['search']['value'] !== '') {
					$search[] = [
						'field' => self::getField($s['data'], $filter),
						'value' => $s['search']['value'],
						'type'  => $s['name']
					];
				}
			}
		}
		
		if (count($search) > 0) {
			$query = '';
			foreach ($search as $q) {
				if ($q['type'] === 'date') {
					$query .= ' ' . htmlspecialchars( "CONVERT(VARCHAR, ".$q['field']." , 120)" ) . ' LIKE \'%' . htmlspecialchars($q['value']) . '%\' AND ';
				}else{
					$query .= ' ' . htmlspecialchars($q['field']) . ' LIKE \'%' . htmlspecialchars($q['value']) . '%\' AND ';
				}
			}
			return trim($query, ' AND ');

		} else {
			return ' 1=1 ';
		}
	}

	public function getField($col, $filter) {
		if ($filter !== null) {
			if (isset($filter[$col])) {
				return $filter[$col];
			} else {
				return $col;
			}
		} else {
			return $col;
		}
	}

	public function get($data, $search) {

		if ( isset($search['order']) ) {
			if ($search['order'][0]['dir'] === 'asc') {
				ksort($data);
			} else {
				krsort($data);
			}
		}

		return [
			'draw' => (int)$search['draw'],
			'recordsTotal' => count($data),
			'recordsFiltered' => count($data),
			"data" => array_slice($data, $search['start'], $search['length'])
    ];
	}
}