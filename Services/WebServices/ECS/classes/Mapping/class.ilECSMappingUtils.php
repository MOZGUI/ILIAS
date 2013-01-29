<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Mapping utils
 * 
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 * $Id$
 */
class ilECSMappingUtils
{
	const MAPPED_WHOLE_TREE = 1;
	const MAPPED_MANUAL = 2;
	const MAPPED_UNMAPPED = 3;


	/**
	 * Lookup mapping status
	 * @param int $a_server_id
	 * @param int $a_tree_id
	 * @return int
	 */
	public static function lookupMappingStatus($a_server_id, $a_mid, $a_tree_id)
	{
		include_once './Services/WebServices/ECS/classes/Mapping/class.ilECSNodeMappingAssignments.php';

		if(ilECSNodeMappingAssignments::hasAssignments($a_server_id, $a_mid, $a_tree_id))
		{
			if(ilECSNodeMappingAssignments::isWholeTreeMapped($a_server_id, $a_mid, $a_tree_id))
			{
				return self::MAPPED_WHOLE_TREE;
			}
			return self::MAPPED_MANUAL;
		}
		return self::MAPPED_UNMAPPED;
	}
	
	/** 
	 * Get mapping status as string
	 * @param int $a_status 
	 */
	public static function mappingStatusToString($a_status)
	{
		global $lng;
		
		return $lng->txt('ecs_node_mapping_status_'.$a_status);
	}
	
	
	public static function getCourseMappingFieldInfo()
	{
		global $lng;
		
		$field_info = array();
		$counter = 0;
		foreach(
			array(
				'organisation',
				'term',
				'title',
				'lecturer') as $field)
		{
			$field_info[$counter]['name'] = $field;
			$field_info[$counter]['translation'] = $lng->txt('ecs_cmap_att_'.$field);
			$counter++;
		}
		return $field_info;
	}
	
	public static function getCourseMappingFieldSelectOptions()
	{
		global $lng;
		
		$options[''] = $lng->txt('select_one');
		foreach(self::getCourseMappingFieldInfo() as $info)
		{
			$options[$info['name']] = $info['translation'];
		}
		return $options;
	}
	
	/**
	 * Get course value by mapping
	 * @param type $course
	 * @param type $a_field
	 */
	public static function getCourseValueByMappingAttribute($course, $a_field)
	{
		switch($a_field)
		{
			case 'organisation':
				return (string) $course->basicData->organisation;
				
			case 'term':
				return (string) $course->basicData->term;
				
			case 'title':
				return (string) $course->basicData->title;
				
			case 'lecturer':
				foreach((array) $course->lecturers as $lecturer)
				{
					return (string) ($lecturer->lastName.', '. $lecturer->firstName);
				}
				return '';
		}
		return '';
	}
	
	
	public static function getRoleMappingInfo()
	{
		include_once './Services/Membership/classes/class.ilParticipants.php';
		return array(
			IL_CRS_ADMIN => array(
				'role' => IL_CRS_ADMIN,
				'lang' => 'il_crs_admin',
				'create' => true,
				'required' => true,
				'type' => 'crs'),
			IL_CRS_TUTOR => array(
				'role' => IL_CRS_TUTOR,
				'lang' => 'il_crs_tutor',
				'create' => true,
				'required' => false,
				'type' => 'crs'),
			IL_CRS_MEMBER => array(
				'role' => IL_CRS_MEMBER,
				'lang' => 'il_crs_member',
				'create' => false,
				'required' => true,
				'type' => 'crs'),
			IL_GRP_ADMIN => array(
				'role' => IL_GRP_ADMIN,
				'lang' => 'il_grp_admin',
				'create' => true,
				'required' => false,
				'type' => 'grp'),
			IL_GRP_MEMBER => array(
				'role' => IL_GRP_MEMBER,
				'lang' => 'il_grp_member',
				'create' => false,
				'required' => false,
				'type' => 'grp')
		);
	}


}
?>
