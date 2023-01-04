<?php

if (!defined('BASEPATH'))
	exit('No direct script access allowed');
date_default_timezone_set('Asia/Bangkok');

class M_User extends CI_Model
{

	public function __construct()
	{
		parent::__construct();
		$this->load->database();
	}

	public function insert_newsletter($email)
	{
		$query = $this->db->query('SELECT * FROM t_newsletter_arcigee WHERE email= ? ', array($email));

		if ($query->num_rows() <= 0) {
			$this->db->query('INSERT INTO t_newsletter_arcigee(email) VALUES(' . $this->db->escape($email) . ')
        ');

			$newsletter_id = $this->db->insert_id();
			return $newsletter_id;
		} else {
			return 'registered';
		}
	}

	public function check_phone_email($phone, $email, $candidate_id = '')
	{
		// $where = '';
		// if ($candidate_id !== '') {
		// 	$where .= 'candidate_id <> ' . $this->db->escape($candidate_id) . ' AND ';
		// }

		// $query = $this->db->query(
		// 	'	SELECT
		// 			candidate_id
		// 		FROM
		// 			t_candidate
		// 		WHERE
		// 		' . $where . '
		// 		( phone = ? OR email = ? ) AND flag_candidate = 1 ',
		// 	array(
		// 		$phone,
		// 		$email,
		// 	)
		// );

		$query = $this->db
			->select('candidate_id')
			->from('t_candidate')
			->group_start()
			->where('phone', $phone)
			->or_where('email', $email)
			->group_end()
			->where('flag_candidate', "1");

		if (!empty($candidate_id)) {
			$this->db->where('candidate_id !=', $candidate_id);
		}

		return $query->get()->num_rows();
	}

	public function check_mobile_no($phone)
	{

		$query = $this->db->query(
			'

      SELECT

      phone

      FROM

      t_candidate

      WHERE

      phone=' . $this->db->escape($phone) . ' AND flag_candidate=1'
		);

		return $query->num_rows();
	}

	public function check_mobile_no_exception($phone, $candidate_id)
	{

		$query = $this->db->query(
			'

      SELECT

      phone

      FROM

      t_candidate

      WHERE

      phone=' . $this->db->escape($phone) . ' AND candidate_id<>' . $candidate_id
		);

		return $query->num_rows();
	}

	public function check_email($email)
	{

		$query = $this->db->query(
			'

      SELECT

      email

      FROM

      t_candidate

      WHERE

      email=' . $this->db->escape($email)
		);

		return $query->num_rows();
	}

	public function check_email_exception($email, $candidate_id)
	{

		$query = $this->db->query(
			'

      SELECT

      email

      FROM

      t_candidate

      WHERE

      email=' . $this->db->escape($email) . ' AND candidate_id<>' . $candidate_id
		);

		return $query->num_rows();
	}

	public function get_jobseeker($column, $value)
	{

		$query = $this->db->query(
			'
      SELECT

      j.*, jf.job_func_desc_indo, "-" as major

      FROM t_candidate j

      LEFT JOIN  m_job_function jf ON j.job_func_id = jf.job_func_id

      -- LEFT JOIN (select * from candidate_educ group by candidate_id order by educ_id desc,candidate_educ_id asc) ce ON ce.candidate_id=j.candidate_id

      WHERE

      j.' . $column . '=' . $this->db->escape($value)
		);



		if ($query->num_rows() > 0) {

			$data = $query->row_array();

			return $data;
		} else {

			return 0;
		}
	}

	public function check_login($email, $password)
	{

		$enPassword = sha1(md5($password));

		$this->db
			->select("
			c.candidate_id,
			c.full_name,
			c.dob,
			c.gender,
			c.lat,
			c.lng,
			c.phone,
			c.email,
			c.photo,
			c.flag_candidate,
			c.registered_from,
			c.socmed_id,
			c.socmed_picture,
			c.job_func_id,
			jf.job_func_desc,
			jf.job_func_desc_indo,
			c.city_id
		")
			->from("t_candidate c")
			->join("m_job_function jf", "c.job_func_id = jf.job_func_id", "LEFT")
			->group_start()
			->where("c.phone", $email)
			->or_where("c.email", $email)
			->group_end()
			->where("c.password", $enPassword);

		$checkCandidate = $this->db->get()->row_array();

		return !empty($checkCandidate) ? $checkCandidate : false;
	}

	public function update_login($candidate_id)
	{

		return $this->db->update(
			't_candidate',
			array(
				'login_date' => date('Y-m-d H:i:s', strtotime("now"))
			),
			array(
				'candidate_id' => $candidate_id
			)
		);
	}

	public function update_notif_token($candidate_id, $notif_token)
	{

		$this->db->query(
			'

      UPDATE t_candidate

      SET notif_token="' . $notif_token . '"

      WHERE candidate_id=' . $this->db->escape($candidate_id)
		);
	}

	public function update_location($candidate_id, $lat, $lng)
	{

		$this->db->query(
			'

      UPDATE t_candidate

      SET lat=' . $this->db->escape($lat) . ',lng=' . $this->db->escape($lng) . ' WHERE candidate_id=' . $this->db->escape($candidate_id)
		);
	}

	public function insert_js(
		$registration_user,
		$institution_name,
		$major,
		$educ_id,
		$overseas_educ,
		$gpa,
		$experience,
		$fresh_graduate,
		$job_func_id,
		$city_id,
		$religion_id,
		$marital_status_id,
		$ethnic,
		$ethnic_id,
		$current_salary,
		$expected_salary,
		$resume_name,
		$company_name,
		$position,
		$start_year,
		$end_year,
		$currently_work,
		$keahlian_name_array,
		$rating_name_array,
		$business_line_array
	) {
		$this->db->query(
			'INSERT INTO t_candidate(
				employer_id,
				full_name, dob,
				email,
				`password`,
				city_id,
				major,
				religion_id,
				gender,
				institution_name,
				experience,
				gpa,
				marital_status_id,
				ethnic,
				ethnic_id,
				educ_id,
				overseas_educ,
				fresh_graduate,
				phone,
				entry_date,
				current_salary,
				expected_salary,
				photo,
				job_func_id,
				preferred_location_id,
        		notif_token,
				lat,
				lng,
				registered_from,
				socmed_id,
				socmed_picture,
				login_date,
				flag_candidate,
				input_flag,
				flag_update,
				cv,
				resume_update)
      	VALUES(
			  	52749,' .
				$this->db->escape($registration_user['full_name']) . ',' .
				$this->db->escape($registration_user['dob']) . ',' .
				$this->db->escape($registration_user['email']) . ',' .
				$this->db->escape((sha1(md5($registration_user['password'])))) . ',' .
				$this->db->escape($city_id) . ',' .
				$this->db->escape($major) . ',' .
				$this->db->escape($religion_id) . ',' .
				$this->db->escape($registration_user['gender']) . ',' .
				$this->db->escape($institution_name) . ',' .
				$this->db->escape($experience) . ',' .
				$this->db->escape($gpa) . ',' .
				$this->db->escape($marital_status_id) . ',' .
				$this->db->escape($ethnic) . ',' .
				$this->db->escape($ethnic_id) . ',' .
				$this->db->escape($educ_id) . ',' .
				$this->db->escape($overseas_educ) . ',' .
				$this->db->escape($fresh_graduate) . ',' .
				$this->db->escape($registration_user['phone']) . ',"' .
				date("Y-m-d H:i:s", strtotime("now")) . '",' .
				$this->db->escape($current_salary) . ',' .
				$this->db->escape($expected_salary) . ',' .
				$this->db->escape($registration_user['photo']) . ',' .
				$this->db->escape($job_func_id) . ',' .
				$this->db->escape($city_id) . ',' .
				$this->db->escape($registration_user['notif_token']) . ',' .
				$this->db->escape($registration_user['lat']) . ',' .
				$this->db->escape($registration_user['lng']) . ',
				"karirsite",' .
				$this->db->escape($registration_user['socmed_id']) . ',' .
				$this->db->escape($registration_user['socmed_picture']) . ',"' .
				date("Y-m-d H:i:s", strtotime("now")) . '",
				1,
				"a",
				"undo",
				' . $this->db->escape($resume_name) . ',"' .
				date("Y-m-d H:i:s", strtotime("now")) . '")'
		);

		$candidate_id = $this->db->insert_id();

		$this->db->query('
      INSERT INTO candidate_educ(candidate_id,educ_id,institution_name,major,gpa,overseas_educ)
      VALUES (' . $this->db->escape($candidate_id) . ',' . $this->db->escape($educ_id) . ',' . $this->db->escape($institution_name) . ',' . $this->db->escape($major) . ',' . $this->db->escape($gpa) . ',' . $this->db->escape($overseas_educ) . ')
      ');

		if (isset($company_name) && !empty($company_name)) {
			foreach ($company_name as $key => $value) {
				$until_value = 0;
				if ($currently_work[$key] == 'true') {
					$until_value = 1;
				}
				if (empty($end_year[$key])) {
					$end_year[$key] = '0000-00-00';
				}
				$this->db->query('INSERT INTO candidate_exp(candidate_id,company_name,start_year,end_year,until_now,position,business_line)
          							VALUES (' . $this->db->escape($candidate_id) . ',' . $this->db->escape($value) . ',' . $this->db->escape($start_year[$key]) . ',' . $this->db->escape($end_year[$key]) . ',' . $this->db->escape($until_value) . ',' . $this->db->escape($position[$key]) . ',' . $this->db->escape($business_line_array[$key]) . ')
          ');
			}
		}

		if (isset($keahlian_name_array) && !empty($keahlian_name_array)) {
			foreach ($keahlian_name_array as $key => $value) {
				if (array_key_exists($key, $rating_name_array)) {
					$this->db->query('
            INSERT INTO candidate_skill(candidate_id,skill,understanding)
            VALUES (' . $this->db->escape($candidate_id) . ',' . $this->db->escape($value) . ',' . $this->db->escape($rating_name_array[$key]) . ')
            ');
				}
			}
		}

		$query = $this->db->query(
			'
      SELECT
      candidate_id, full_name, dob, gender, lat, lng, phone, email, photo, registered_from,socmed_id,socmed_picture,c.job_func_id,job_func_desc,city_id
      FROM
      t_candidate c
      LEFT JOIN m_job_function jf ON jf.job_func_id=c.job_func_id
      WHERE
      candidate_id=' . $this->db->escape($candidate_id)
		);

		return $query->row_array();
	}

	public function insert_js_to_candidate_access($canId)
	{
		$this->db->query('INSERT INTO candidate_access VALUES (' . $canId . ', 52749, "' . date("Y-m-d H:i:s", strtotime("now")) . '")');
	}

	public function update_js_resume($candidate_id, $cv)
	{

		$this->db->query(
			'

      UPDATE t_candidate

      SET cv=' . $this->db->escape($cv) . ',modified="' . date("Y-m-d H:i:s", strtotime("now")) . '",resume_update="' . date("Y-m-d H:i:s", strtotime("now")) . '"  WHERE candidate_id=' . $this->db->escape($candidate_id)
		);
	}

	public function get_jobseeker_detail($id)
	{

		$where = "j.candidate_id=" . $this->db->escape($id);

		$this->db->select(
			'
          j.*,
          (DATE_FORMAT(NOW(), "%Y") - DATE_FORMAT(j.dob, "%Y") - (DATE_FORMAT(NOW(), "00-%m-%d") < DATE_FORMAT(j.dob, "00-%m-%d"))) age,
        id_number,
        id_desc,
		privy_username,
		ms.desc as home_status_desc,
        ci.hobby,
        photo,
        j.socmed_picture,
        j.phone,home_address,
        j.current_salary,
        j.resume_update,
        j.id_type,
        j.fresh_graduate,
        jf.job_func_desc_indo,
        r.religion_desc,
        r.religion_desc_en,
        e.ethnic_desc,
        ed.desc as educ_desc,
        ed.code as educ_code,
        j.major,
		mj.major_name,
		j.educ_graduation,
        m.desc as marital_status_desc,
        m.id_data as marital_status_id,
        m.code as marital_code,
        c.city_name,
        p.province_name,
        d.district_name,
        v.village_name,
        cp.city_id as preferred_city_id,
        cp.city_name as preferred_city_name,
        pp.province_name as preferred_province_name,
		j.cv as candidate_cv,
		an.code as nationality_code,
		an.desc as nationality_desc,
		atc.no_bpjs,
		atc.no_ponta,
		atc.internal_reference,
		atc.external_reference'
		);


		$this->db->from('t_candidate j');

		//       $this->db->join('(select * from candidate_educ group by candidate_id order by educ_id desc,candidate_educ_id asc) ce', 'ce.candidate_id=j.candidate_id', 'left');

		$this->db->join('m_city c', 'j.city_id=c.new_city_id', 'left');

		$this->db->join('m_district d', 'j.sub_district_id=d.district_id', 'left');

		$this->db->join('m_village v', 'j.village_id=v.village_id', 'left');

		$this->db->join('m_province p', 'j.province_id=p.province_id', 'left');

		$this->db->join('m_city cp', 'j.preferred_location_id=cp.city_id', 'left');

		$this->db->join('m_province pp', 'cp.province_id=pp.province_id', 'left');

		$this->db->join('m_major mj', 'j.major=mj.major_id', 'left');

		$this->db->join('m_job_function jf', 'j.job_func_id=jf.job_func_id', 'left');

		$this->db->join('m_religion r', 'j.religion_id=r.religion_id', 'left');
		$this->db->join('alfa_nationality an', 'j.nationality=an.id_data', 'left');

		$this->db->join('m_ethnic e', 'j.ethnic_id=e.ethnic_id', 'left');
		$this->db->join('candidate_other_information ci', 'j.candidate_id=ci.candidate_id', 'left');

		$this->db->join('m_id', 'm_id.id_type=j.id_type', 'left');
		$this->db->join('alfa_marriage_status m', 'j.marital_status_id=m.id_data', 'left');

		$this->db->join('alfa_formal_education ed', 'j.educ_id=ed.id_data', 'left');

		$this->db->join('alfa_home_status ms', 'j.home_status=ms.id', 'left');
		
		$this->db->join('alfa_t_candidate atc', 'atc.t_candidate_id=j.candidate_id', 'left');

		$this->db->join('t_employer te', 'te.employer_id=j.employer_id', 'left');

		$this->db->where($where);

		$query = $this->db->get();



		if ($query->num_rows() > 0) {

			return $query->row_array();
		}

		return false;
	}

	public function get_candidate($id)
	{
		$where = "j.candidate_id=" . $this->db->escape($id);

		$this->db->select('j.*, (DATE_FORMAT(NOW(), "%Y") - DATE_FORMAT(j.dob, "%Y") - (DATE_FORMAT(NOW(), "00-%m-%d") < DATE_FORMAT(j.dob, "00-%m-%d"))) age, c.city_name, p.province_name');
		$this->db->from('t_candidate j');
		$this->db->join('m_city c', 'j.city_id=c.city_id', 'left');
		$this->db->join('m_province p', 'c.province_id=p.province_id', 'left');
		$this->db->where($where);

		$query = $this->db->get();
		if ($query->num_rows() > 0) {
			return $query->row_array();
		}

		return false;
	}

	// INCHANGE
	// update the candidate flag
	public function update_flag_candidate($can_id, $flag)
	{
		$save['flag_update'] = $flag;
		$this->db->update('t_candidate', $save, array('candidate_id' => $can_id));
	}

	public function get_jobseeker_experience($candidate_id)
	{
		$query = $this->db->query(
			'
      SELECT *
      FROM candidate_exp
      WHERE candidate_id=' . $this->db->escape($candidate_id) . '
      ORDER BY seq_no ASC'
		);

		if ($query->num_rows() > 0) {

			$data = $query->result_array();

			return $data;
		} else {

			return 0;
		}
	}

	public function get_jobseeker_formal($candidate_id)
	{
		$query = $this->db->query(
			'
      SELECT *
      FROM candidate_educ a LEFT JOIN alfa_formal_education b ON a.alfa_educ_id=b.id_data LEFT JOIN m_major mj on a.major=mj.major_id left join m_city mc on a.city_id=mc.city_id
      WHERE candidate_id=' . $this->db->escape($candidate_id) . '
      ORDER BY end_year ASC'
		);
 
			$data = $query->result_array();

			return $data; 
	}

	public function get_jobseeker_nonformal($candidate_id)
	{
		$query = $this->db->query(
			'
      SELECT *
      FROM candidate_course
      WHERE candidate_id=' . $this->db->escape($candidate_id) . '
      ORDER BY seq_no ASC'
		);

		if ($query->num_rows() > 0) {

			$data = $query->result_array();

			return $data;
		} else {

			return 0;
		}
	}

	public function get_jobseeker_skill($candidate_id)
	{
		$query = $this->db->query(
			'
      SELECT *
      FROM candidate_skill cs inner join alfa_hard_skill ah on cs.skill=ah.id_data
      WHERE cs.candidate_id=' . $this->db->escape($candidate_id) . '
      ORDER BY cs.understanding DESC'
		);

		if ($query->num_rows() > 0) {

			$data = $query->result_array();

			return $data;
		} else {

			return 0;
		}
	}

	public function get_jobseeker_kontak($candidate_id)
	{
		$query = $this->db->query(
			'
      SELECT *
      FROM candidate_emergency_call
      WHERE candidate_id=' . $this->db->escape($candidate_id) . ''
		);

		if ($query->num_rows() > 0) {

			$data = $query->result_array();

			return $data;
		} else {

			return 0;
		}
	}

	public function get_jobseeker_organization($candidate_id)
	{
		$query = $this->db->query(
			'
      SELECT org_desc,org_name,org_member,start_year,end_year
      FROM candidate_org
      WHERE candidate_id=' . $this->db->escape($candidate_id) . '
      ORDER BY seq_no DESC'
		);

		if ($query->num_rows() > 0) {

			$data = $query->result_array();

			return $data;
		} else {

			return 0;
		}
	}

	public function update_js($candidate_id, $full_name, $dob, $age, $email, $gender, $phone, $institution_name, $major, $educ_id, $overseas_educ, $gpa, $experience, $fresh_graduate, $job_func_id, $city_id, $religion_id, $marital_status_id, $ethnic, $ethnic_id, $current_salary, $expected_salary, $company_name, $position, $start_year, $end_year, $currently_work, $cv, $photo, $resume_update, $keahlian_name_array, $rating_name_array)
	{

		$this->db->query(
			'
      UPDATE t_candidate
      SET
      full_name=' . $this->db->escape($full_name) . ',
      dob=' . $this->db->escape($dob) . ',
      email=' . $this->db->escape($email) . ',
      gender=' . $this->db->escape($gender) . ',
      phone=' . $this->db->escape($phone) . ',
      institution_name=' . $this->db->escape($institution_name) . ',
      educ_id=' . $this->db->escape($educ_id) . ',
      overseas_educ=' . $this->db->escape($overseas_educ) . ',
      fresh_graduate=' . $this->db->escape($fresh_graduate) . ',
      gpa=' . $this->db->escape($gpa) . ',
      experience=' . $this->db->escape($experience) . ',
      job_func_id=' . $this->db->escape($job_func_id) . ',
      major=' . $this->db->escape($major) . ',
      religion_id=' . $this->db->escape($religion_id) . ',
      marital_status_id=' . $this->db->escape($marital_status_id) . ',
      preferred_location_id=' . $this->db->escape($city_id) . ',
      ethnic=' . $this->db->escape($ethnic) . ',
      ethnic_id=' . $this->db->escape($ethnic_id) . ',
      current_salary=' . $this->db->escape($current_salary) . ',
      expected_salary=' . $this->db->escape($expected_salary) . ',

      cv=' . $this->db->escape($cv) . ',
      photo=' . $this->db->escape($photo) . ',
      resume_update=' . $this->db->escape(date('Y-m-d H:i:s', strtotime("now"))) . ',
      modified=' . $this->db->escape(date('Y-m-d H:i:s', strtotime("now"))) . ' WHERE candidate_id=' . $this->db->escape($candidate_id)
		);

		$check_educ_query = $this->db->query(
			'
      SELECT
      candidate_id
      FROM
      candidate_educ
      WHERE
      candidate_id=' . $this->db->escape($candidate_id)
		);
		$check_educ       = $check_educ_query->num_rows();

		if ($check_educ > 0) {
			$this->db->query(
				'
        UPDATE candidate_educ
        SET
        educ_id=' . $this->db->escape($educ_id) . ',
        institution_name=' . $this->db->escape($institution_name) . ',
        major=' . $this->db->escape($major) . ',
        gpa=' . $this->db->escape($gpa) . ',
        overseas_educ=' . $this->db->escape($overseas_educ) . ' WHERE candidate_id=' . $this->db->escape($candidate_id)
			);
		} else {
			$this->db->query('
        INSERT INTO candidate_educ(candidate_id,educ_id,institution_name,major,gpa,overseas_educ)
        VALUES (' . $this->db->escape($candidate_id) . ',' . $this->db->escape($educ_id) . ',' . $this->db->escape($institution_name) . ',' . $this->db->escape($major) . ',' . $this->db->escape($gpa) . ',' . $this->db->escape($overseas_educ) . ')
        ');
		}

		$check_exp_query = $this->db->query(
			'
      SELECT
      candidate_id
      FROM
      candidate_exp
      WHERE
      candidate_id=' . $this->db->escape($candidate_id)
		);
		$check_exp       = $check_exp_query->num_rows();

		if ((isset($company_name) && !empty($company_name)) && $fresh_graduate !== 1) {
			if ($check_exp > 0) {
				$this->db->query(
					'
          DELETE FROM candidate_exp
          WHERE candidate_id=' . $this->db->escape($candidate_id)
				);
			}

			foreach ($company_name as $key => $value) {
				if (array_key_exists($key, $currently_work) && array_key_exists($key, $position) && array_key_exists($key, $start_year) && array_key_exists($key, $end_year)) {
					$until_value = 0;
					if ($currently_work[$key] == 'true') {
						$until_value = 1;
					}

					$this->db->query('
            INSERT INTO candidate_exp(candidate_id,company_name,start_year,end_year,until_now,position)
            VALUES (' . $this->db->escape($candidate_id) . ',' . $this->db->escape($value) . ',' . $this->db->escape($start_year[$key]) . ',' . $this->db->escape($end_year[$key]) . ',' . $this->db->escape($until_value) . ',' . $this->db->escape($position[$key]) . ')
            ');
				}
			}
		}


		if (isset($keahlian_name_array) && !empty($keahlian_name_array)) {

			$check_keahlian_query = $this->db->query(
				'
        SELECT
        candidate_id
        FROM
        candidate_skill
        WHERE
        candidate_id=' . $this->db->escape($candidate_id)
			);
			$check_keahlian       = $check_keahlian_query->num_rows();

			if ($check_keahlian > 0) {
				$this->db->query(
					'
          DELETE FROM candidate_skill
          WHERE candidate_id=' . $this->db->escape($candidate_id)
				);
			}

			foreach ($keahlian_name_array as $key => $value) {
				if (array_key_exists($key, $rating_name_array)) {
					$this->db->query('
            INSERT INTO candidate_skill(candidate_id,skill,understanding)
            VALUES (' . $this->db->escape($candidate_id) . ',' . $this->db->escape($value) . ',' . $this->db->escape($rating_name_array[$key]) . ')
            ');
				}
			}
		}

		if ($fresh_graduate == 1) {
			if ($check_exp > 0) {
				$this->db->query(
					'
          DELETE FROM candidate_exp
          WHERE candidate_id=' . $this->db->escape($candidate_id)
				);
			}
		}

		$query = $this->db->query('
      SELECT
      c.candidate_id, c.full_name, c.dob, c.gender, c.lat, c.lng, c.phone, c.email, c.photo, c.registered_from,c.socmed_id,c.socmed_picture, c.job_func_id, jf.job_func_desc_indo, jf.job_func_desc,c.city_id
      FROM
      t_candidate c
      LEFT JOIN m_job_function jf ON jf.job_func_id=c.job_func_id
      WHERE
      candidate_id=' . $this->db->escape($candidate_id));
		return $query->row_array();
	}

	public function updatephoto($candidate_id, $photo)
	{
		$foto['photo'] = $photo;
		$this->db->update('t_candidate', $foto, array('candidate_id' => $candidate_id));
	}

	public function get_up_institution($institute_name)
	{
		$query = $this->db->query("
      SELECT university_name, COUNT(*) AS institution_count
        FROM m_university
        WHERE university_name <> ''
        AND university_name LIKE '%" . $this->db->escape_like_str($institute_name) . "%'
        GROUP BY university_name
        ORDER BY institution_count DESC
    ");

		if ($query->num_rows() > 0) {
			return $query->result_array();
		} else {
			return 0;
		}
	}

	function update_personal_profile(
		$id,
		$city_id,
		$id_type,
		$number_type,
		$domisil,
		$negara,
		$fullname,
		$dob,
		$age,
		$email,
		$gender,
		$hp,
		$ins,
		$educ,
		$gpa,
		$jb_func,
		$jb_loc,
		$status,
		$agama,
		$etnis,
		$etnis_id,
		$salary,
		$salary_now,
		$major,
		$overseas,
		$resume_date,
		$fresh
	) {
		$sql = "
    UPDATE t_candidate
    SET city_id=" . $city_id . ", id_type=" . $id_type . ", id_number='" . $number_type . "', home_address= '" . $domisil . "', nationality = '" . $negara . "',
    full_name='" . $fullname . "', dob='" . $dob . "', email='" . $email . "', gender='" . $gender . "', phone='" . $hp . "', institution_name='" . $ins . "', educ_id='" . $educ . "',
    gpa='" . $gpa . "', job_func_id='" . $jb_func . "', preferred_location_id='" . $jb_loc . "', marital_status_id='" . $status . "', religion_id='" . $agama . "',
    ethnic='" . $etnis . "', ethnic_id='" . $etnis_id . "',current_salary='" . $salary_now . "', expected_salary='" . $salary . "'";

		//   if($this->session->userdata("status_profile") > 0){
		//       $sql .= ", resume_update='".$resume_date."'";
		//   }

		$sql .= ",overseas_educ='" . $overseas . "',fresh_graduate='" . $fresh . "'WHERE candidate_id=" . $id;
		//  echo $sql;exit;
		$this->db->query($sql);

		// echo "UPDATE t_candidate SET full_name='".$fullname."', dob='".$dob."', age='".$age."', email='".$email."', gender='".$gender."', phone='".$hp."', institution_name='".$ins."', educ_id='".$educ."', gpa='".$gpa."', job_func_id='".$jb_func."', preferred_location_id='".$jb_loc."',city_id='".$jb_loc."', marital_status_id='".$status."', religion_id='".$agama."',ethnic='".$etnis."', ethnic_id='".$etnis_id."',current_salary='".$salary_now."', expected_salary='".$salary."', resume_update='".$resume_date."',overseas_educ='".$overseas."' WHERE candidate_id=".$id;exit;

		$this->session->set_userdata("userName", ucwords(strtolower($fullname)));
		$this->session->set_userdata("email", $email);
		$query = $this->db->query("SELECT * FROM candidate_educ WHERE candidate_id=" . $id . " AND educ_id=" . $educ);
		$rows  = 0;
		$rows  = $query->num_rows();
		if ($rows > 0) {
			$this->db->query("UPDATE candidate_educ SET major = '" . $major . "',institution_name = '" . $ins . "' WHERE candidate_id=" . $id . " AND educ_id=" . $educ);
		} else {
			$this->db->query("INSERT candidate_educ(candidate_id,educ_id, institution_name, major) VALUES (" . $id . "," . (int) $educ . ",'" . $ins . "','" . $major . "')");
		}
		// $this->db->close();
		return true;
	}

	// function update_personal_profile($id, $city_id, $id_type, $number_type, $domisil, $negara, $fullname, $dob, $age, $email, $gender, $hp, $ins, $educ, $gpa,
	// $jb_func, $jb_loc, $status, $agama, $etnis, $etnis_id, $salary, $salary_now, $major, $overseas, $resume_date, $fresh) {
	//   $sql= "
	//   UPDATE t_candidate
	//   SET city_id=".$city_id.", id_type=".$id_type.", id_number='".$number_type."', home_address= '".$domisil."', nationality = '".$negara."',
	//   full_name='".$fullname."', dob='".$dob."', email='".$email."', gender='".$gender."', phone='".$hp."', institution_name='".$ins."', educ_id='".$educ."',
	//   gpa='".$gpa."', job_func_id='".$jb_func."', preferred_location_id='".$jb_loc."', marital_status_id='".$status."', religion_id='".$agama."',
	//   ethnic='".$etnis."', ethnic_id='".$etnis_id."',current_salary='".$salary_now."', expected_salary='".$salary."'";
	//
	//   //   if($this->session->userdata("status_profile") > 0){
	//   //       $sql .= ", resume_update='".$resume_date."'";
	//   //   }
	//
	//   $sql .= ",overseas_educ='".$overseas."',fresh_graduate='".$fresh."'WHERE candidate_id=".$id;
	//   //  echo $sql;exit;
	//   $this->db->query($sql);
	//
	//   // echo "UPDATE t_candidate SET full_name='".$fullname."', dob='".$dob."', age='".$age."', email='".$email."', gender='".$gender."', phone='".$hp."', institution_name='".$ins."', educ_id='".$educ."', gpa='".$gpa."', job_func_id='".$jb_func."', preferred_location_id='".$jb_loc."',city_id='".$jb_loc."', marital_status_id='".$status."', religion_id='".$agama."',ethnic='".$etnis."', ethnic_id='".$etnis_id."',current_salary='".$salary_now."', expected_salary='".$salary."', resume_update='".$resume_date."',overseas_educ='".$overseas."' WHERE candidate_id=".$id;exit;
	//
	//   $this->session->set_userdata("userName",ucwords(strtolower($fullname)));
	//   $this->session->set_userdata("email",$email);
	//   $query = $this->db->query("SELECT * FROM candidate_educ WHERE candidate_id=".$id." AND educ_id=".$educ);
	//   $rows = 0;
	//   $rows = $query->num_rows();
	//   if($rows > 0)
	//   {
	//     $this->db->query("UPDATE candidate_educ SET major = '".$major."',institution_name = '".$ins."' WHERE candidate_id=".$id." AND educ_id=".$educ);
	//   }
	//   else{
	//     $this->db->query("INSERT candidate_educ(candidate_id,educ_id, institution_name, major) VALUES (".$id.",".(int)$educ.",'".$ins."','".$major."')");
	//   }
	//   // $this->db->close();
	//   return true;
	// }

	function delete_exp_personal($id)
	{
		$sql = "DELETE FROM candidate_exp WHERE candidate_id='$id'";
		$this->db->query($sql);
		$this->db->query("UPDATE t_candidate SET current_salary='0' WHERE candidate_id='" . (int) $id . "'");
		return true;
	}

	function update_exp_time($id, $exp)
	{
		$this->db->query("UPDATE t_candidate SET experience='" . (int) $exp . "' WHERE candidate_id='" . (int) $id . "'");
		return true;
	}

	function get_resume_name($candidate_id)
	{
		$query = $this->db->query("SELECT cv,entry_date FROM t_candidate WHERE candidate_id=" . $candidate_id . "");
		$rows  = 0;
		$rows  = $query->num_rows();
		if ($rows > 0) {
			return $query->row_array();
		} else {
			return array();
		}
	}

	function check_experience($id)
	{
		$query = $this->db->query("SELECT seq_no FROM candidate_exp WHERE seq_no=" . (int) $id);
		$rows  = 0;
		$rows  = $query->num_rows();
		$this->db->close();
		if ($rows > 0) {
			return $rows;
		} else {
			return 0;
		}
	}

	function update_experience($id, $company, $position, $start_year, $end_year, $until_now, $lokasi, $bu, $alasan, $gaji, $job_desc, $prestasi)
	{
		$data2['company_name']       = $company;
		$data2['position']           = $position;
		$data2['start_year']         = $start_year;
		$data2['end_year']           = $end_year;
		$data2['until_now']          = $until_now;
		$data2['company_address']    = $lokasi;
		$data2['business_line']      = $bu;
		$data2['reason_for_leaving'] = $alasan;
		$data2['last_drawn_salary']  = $gaji;
		$data2['job_desc']           = $job_desc;
		$data2['achievement']        = $prestasi;


		$this->db->where('seq_no', $id);
		$this->db->update('candidate_exp', $data2);
		$this->db->_reset_write();
	}



	function update_fresh_graduated($id, $fresh)
	{
		$this->db->query("UPDATE t_candidate SET fresh_graduate='" . $fresh . "' WHERE candidate_id='" . (int) $id . "'");
		return true;
	}

	function delete_other_experience($id)
	{
		//    echo var_dump($id);exit;
		if (count($id) > 0) {
			$sql = "DELETE FROM candidate_exp WHERE";
			$i   = 0;
			foreach ($id as $idexp) {
				if ($i == 0) {
					$sql .= " seq_no != " . (int) $idexp;
				} else {
					$sql .= " AND seq_no != " . (int) $idexp;
				}
				$i++;
			}
			$sql .= " AND candidate_id = " . (int) $this->session->userdata('id');
			//  echo $sql;exit;
			$this->db->query($sql);
			return true;
		}
	}

	function get_organisasi($id)
	{
		$query = $this->db->query("SELECT * FROM candidate_org WHERE candidate_id=" . $id . " ORDER BY seq_no ASC");
		$rows  = 0;
		$rows  = $query->num_rows();
		if ($rows > 0) {
			return $query->result_array();
		} else {
			return array();
		}
	}

	function deleteOtherorganisasi()
	{
		// echo var_dump($id);exit;

		$sql = "DELETE FROM candidate_org WHERE candidate_id = " . (int) $this->session->userdata('id');

		$this->db->query($sql);
		return true;
	}

	function get_formal_educ($id)
	{
		$query = $this->db->query("SELECT * FROM candidate_educ WHERE candidate_id=" . $id . " ORDER BY candidate_educ_id ASC");
		$rows  = 0;
		$rows  = $query->num_rows();
		if ($rows > 0) {
			return $query->result_array();
		} else {
			return array();
		}
	}

	function check_formal_educ($id)
	{
		$query = $this->db->query("SELECT candidate_educ_id FROM candidate_educ WHERE candidate_educ_id='" . $id . "'");
		//    echo "SELECT candidate_educ_id FROM candidate_educ WHERE candidate_educ_id=".$id;
		$rows  = 0;
		$rows  = $query->num_rows();
		if ($rows > 0) {
			return $rows;
		} else {
			return 0;
		}
	}

	function editFormalEduc($ed_id, $ins, $major, $educ, $gpa, $start, $end, $overseas)
	{
		$this->db->query("UPDATE candidate_educ SET educ_id=" . $educ . ", institution_name='" . $ins . "', major='" . $major . "', start_year='" . $start . "', end_year='" . $end . "', gpa=" . $gpa . ", overseas_educ=" . $overseas . " WHERE candidate_educ_id=" . (int) $ed_id);

		$query = $this->db->query("SELECT * FROM t_candidate WHERE candidate_id=" . $this->session->userdata("id") . " AND educ_id=" . $educ);
		$rows  = 0;
		$rows  = $query->num_rows();
		if ($rows > 0) {
			$this->db->query("UPDATE t_candidate SET major = '" . $major . "',institution_name = '" . $ins . "' WHERE candidate_id=" . $this->session->userdata("id") . " AND educ_id=" . $educ);
		}
		return true;
	}

	function insertFormalEduc($id, $ins, $major, $educ, $gpa, $start, $end, $overseas)
	{
		$this->db->query("INSERT INTO candidate_educ (candidate_id, educ_id, institution_name, major, start_year, end_year, gpa,overseas_educ) VALUES (" . $id . ", " . $educ . ",'" . $ins . "','" . $major . "','" . $start . "','" . $end . "'," . $gpa . "," . $overseas . ")");
		$insert_id = $this->db->insert_id();
		return $insert_id;
	}

	function deleteOtherFormalEduc($id)
	{
		// echo var_dump($id);exit;
		if (count($id) > 0) {
			$sql = "DELETE FROM candidate_educ WHERE";
			$i   = 0;
			foreach ($id as $idexp) {
				if ($i == 0) {
					$sql .= " candidate_educ_id != " . (int) $idexp;
				} else {
					$sql .= " AND candidate_educ_id != " . (int) $idexp;
				}
				$i++;
			}
			$sql .= " AND candidate_id = " . (int) $this->session->userdata('id');
			//  echo $sql;exit;
			$this->db->query($sql);
			return true;
		}
	}

	function get_informal_educ($id)
	{
		$query = $this->db->query("SELECT * FROM candidate_course WHERE candidate_id=" . $id . " ORDER BY seq_no ASC");
		$rows  = 0;
		$rows  = $query->num_rows();
		if ($rows > 0) {
			return $query->result_array();
		} else {
			return array();
		}
	}

	function check_informal_educ($id)
	{
		$query = $this->db->query("SELECT seq_no FROM candidate_course WHERE seq_no='" . $id . "'");
		$rows  = 0;
		$rows  = $query->num_rows();
		if ($rows > 0) {
			return $rows;
		} else {
			return 0;
		}
	}

	function editInformalEduc($topic, $organization, $location, $sertificate, $start, $end, $seq)
	{
		$this->db->query("UPDATE  candidate_course SET topic='" . $topic . "', organized_by='" . $organization . "', location='" . $location . "', certificate='" . $sertificate . "', start_year='" . date('Y-m-d', strtotime($start)) . "', end_year='" . date('Y-m-d', strtotime($end)) . "' WHERE seq_no=" . $seq);
		return true;
	}

	function insertInformalEduc($topic, $organization, $location, $sertificate, $start, $end, $id)
	{

		$this->db->query("INSERT INTO candidate_course (candidate_id, topic, organized_by, location, certificate, start_year, end_year) VALUES (" . $id . ", '" . $topic . "','" . $organization . "','" . $location . "','" . $sertificate . "','" . date('Y-m-d', strtotime($start)) . "','" . date('Y-m-d', strtotime($end)) . "')");
		$insert_id = $this->db->insert_id();
		return $insert_id;
	}

	function deleteOtherInformalEduc($id)
	{
		// echo var_dump($id);exit;
		if (count($id) > 0) {
			$sql = "DELETE FROM candidate_course WHERE";
			$i   = 0;
			foreach ($id as $idexp) {
				if ($i == 0) {
					$sql .= " seq_no != " . (int) $idexp;
				} else {
					$sql .= " AND seq_no != " . (int) $idexp;
				}
				$i++;
			}
			$sql .= " AND candidate_id = " . (int) $this->session->userdata('id');
			//  echo $sql;exit;
			$this->db->query($sql);
			return true;
		}
	}

	function get_skill_detail($id)
	{
		$query = $this->db->query("SELECT * FROM candidate_skill WHERE candidate_id=" . $id . "");
		$rows  = 0;
		$rows  = $query->num_rows();
		if ($rows > 0) {
			return $query->result_array();
		} else {
			return array();
		}
	}

	function check_skill_detail($id)
	{
		$query = $this->db->query("SELECT seq_no FROM candidate_skill WHERE seq_no='" . $id . "'");
		$rows  = 0;
		$rows  = $query->num_rows();
		if ($rows > 0) {
			return $rows;
		} else {
			return 0;
		}
	}

	function edit_skill_detail($language, $understanding, $seq)
	{
		$this->db->query("UPDATE candidate_skill SET skill='" . $language . "', understanding='" . $understanding . "' WHERE seq_no=" . $seq);
		return true;
	}

	function insert_skill_detail($language, $understanding, $id)
	{
		$this->db->query("INSERT INTO candidate_skill (candidate_id, skill, understanding) VALUES (" . $id . ",'" . $language . "','" . $understanding . "')");
		$insert_id = $this->db->insert_id();
		return $insert_id;
	}

	function deleteOther_skill_detail($id)
	{
		// echo var_dump($id);exit;
		if (count($id) > 0) {
			$sql = "DELETE FROM candidate_skill WHERE";
			$i   = 0;
			foreach ($id as $idexp) {
				if ($i == 0) {
					$sql .= " seq_no != " . (int) $idexp;
				} else {
					$sql .= " AND seq_no != " . (int) $idexp;
				}
				$i++;
			}
			$sql .= " AND candidate_id = " . (int) $this->session->userdata('id');
			//  echo $sql;exit;
			$this->db->query($sql);
			return true;
		}
	}

	public function hobby($candidate, $hobby)
	{
		$this->db->select('hobby');
		$hobi = $this->db->get_where('candidate_other_information', array('candidate_id' => $candidate));
		if ($hobi->num_rows() > 0) {
			$update['hobby'] = $hobby;
			$this->db->update('candidate_other_information', $update, array('candidate_id' => $candidate));
		} else {
			$insert['candidate_id'] = $candidate;
			$insert['hobby']        = $hobby;
			$this->db->insert('candidate_other_information', $insert);
		}
	}

	public function get_family($candidateId)
	{
		$returnData = "";

		$checkData = $this->db->query("SELECT * FROM candidate_family_detail a left join alfa_family_relationship b on a.relationship_id=b.id_data WHERE candidate_id = {$candidateId}");

		if ($checkData->num_rows() > 0) {
			$returnData = $checkData->result();
		}

		return $returnData;
	}

	public function get_alfa_family($candidateId)
	{
		$returnData = "";

		$checkData = $this->db->query("SELECT * FROM candidate_family_detail a
		left join alfa_family_relationship b on a.relationship_id = b.id_data WHERE candidate_id = {$candidateId}");

		if ($checkData->num_rows() > 0) {
			$returnData = $checkData->result();
		}

		return $returnData;
	}

	public function get_relationship()
	{
		$return = $this->db->query("SELECT * FROM m_relationship")->result_array();

		return $return;
	}

	public function get_alfa_relationship($status_marriage)
	{
		if ($status_marriage == "") {
			$where = "";
		} elseif ($status_marriage == 1) {
			$where = "AND is_marriage=0";
		}

		$return = $this->db->query("SELECT * FROM alfa_family_relationship WHERE employer_id = '" . EMPLOYER_ID . "' $where")->result_array();

		return $return;
	}

	public function get_employer($column, $value)
	{

		$query = $this->db->query(
			'

                  SELECT
                    e.employer_id, e.employer_name, e.email, e.employee_name, e.employer_address, e.employer_profile, e.lat, e.lng, e.phone, e.mobile_phone, e.employer_logo, e.socmed_picture,e.registered_from, e.free,e.employee_email,e.cpa_credit,e.input_date,e.city_desc,c.city_name,p.province_name,e.city_id,e.password,e.firebase, e.subscribe_status
                  FROM
                    t_employer e
                  LEFT JOIN m_city c ON c.city_id = e.city_id
                  LEFT JOIN m_province p ON p.province_id = c.province_id

                  WHERE

                    ' . $column . '=' . $this->db->escape($value)
		);



		if ($query->num_rows() > 0) {

			$data = $query->row_array();

			return $data;
		} else {

			return 0;
		}
	}

	public function updateresume($candidate, $resume)
	{
		$update['cv'] = $resume;
		$this->db->update('t_candidate', $update, array('candidate_id' => $candidate));
	}

	public function get_jobseeker_formal_education($id)
	{
		$query = $this->db->query("SELECT b.educ_desc,a.institution_name,a.major,a.gpa,a.start_year,a.end_year FROM candidate_educ a LEFT JOIN m_education b ON a.educ_id=b.educ_id where a.candidate_id='" . $id . "' ORDER BY a.start_year ASC");
		$rows  = 0;
		$rows  = $query->num_rows();
		if ($rows > 0) {
			return $query->result_array();
		} else {
			return array();
		}
	}

	public function get_jobseeker_nonformal_education($id)
	{
		$query = $this->db->query("SELECT * FROM candidate_course WHERE candidate_id=" . $id . " ORDER BY seq_no ASC");
		$rows  = 0;
		$rows  = $query->num_rows();
		if ($rows > 0) {
			return $query->result_array();
		} else {
			return array();
		}
	}

	public function check_forgot_password($candidate_id)
	{

		$query = $this->db->query('

                  SELECT
                    *
                  FROM
                    candidate_forgot_password
                  WHERE
                    candidate_id=' . $this->db->escape($candidate_id) . ' AND
                    success_status= "0"
                ');

		if ($query->num_rows() > 0) {
			$data = $query->row_array();
			return $data;
		} else {
			return 0;
		}
	}

	public function forgot_password($candidate_id, $request_date)
	{

		$this->db->query(
			'

            INSERT INTO candidate_forgot_password(candidate_id,request_date)

            VALUES (' . $this->db->escape($candidate_id) . ',' . $this->db->escape($request_date) . ')'
		);
	}

	public function get_jobseeker_forgot_password($candidate_id, $request_date)
	{

		$query = $this->db->query('

                  SELECT

                    *

                  FROM

                    candidate_forgot_password

                  WHERE

                    candidate_id=' . $this->db->escape($candidate_id) . ' AND

                    request_date=' . $this->db->escape($request_date) . ' AND

                    success_status=0

                ');

		if ($query->num_rows() > 0) {

			$data = $query->row_array();

			return $data;
		} else {

			return 0;
		}
	}

	public function update_js_password($candidate_id, $password)
	{

		$this->db->query(
			'

            UPDATE t_candidate

            SET

              password=' . $this->db->escape((sha1(md5($password)))) . ',modified=' . $this->db->escape(date('Y-m-d H:i:s', strtotime("now"))) . '

            WHERE candidate_id=' . $this->db->escape($candidate_id)
		);
	}

	public function forgot_password_success($candidate_id)
	{

		$this->db->query(
			'

            UPDATE candidate_forgot_password

            SET success_status=1,success_date=' . $this->db->escape(date('Y-m-d H:i:s', strtotime("now"))) . '

            WHERE candidate_id=' . $this->db->escape($candidate_id)
		);
	}


	public function delete_disabilty($candidate_id)
	{
		$this->db->delete('alfa_t_candidate_disability', array('candidate_id' => $candidate_id));
	}

	public function delete_disease($candidate_id)
	{
		$this->db->delete('alfa_t_candidate_disease', array('candidate_id' => $candidate_id));
	}

	public function delete_id($candidate_id)
	{
		$this->db->delete('alfa_t_candidate_id', array('candidate_id' => $candidate_id));
	}


	public function delete_experience($candidate_id)
	{
		$this->db->delete('candidate_exp', array('candidate_id' => $candidate_id));
	}

	public function delete_formal($candidate_id)
	{
		$this->db->delete('candidate_educ', array('candidate_id' => $candidate_id));
	}

	public function delete_nonformal($candidate_id)
	{
		$this->db->delete('candidate_course', array('candidate_id' => $candidate_id));
	}

	public function delete_org($candidate_id)
	{
		$this->db->delete('candidate_org', array('candidate_id' => $candidate_id));
	}

	public function delete_skill($candidate_id)
	{
		$this->db->delete('candidate_skill', array('candidate_id' => $candidate_id));
	}

	public function delete_kontak($candidate_id)
	{
		$this->db->delete('candidate_emergency_call', array('candidate_id' => $candidate_id));
	}

	public function delete_keluarga($candidate_id)
	{
		$this->db->delete('candidate_family_detail', array('candidate_id' => $candidate_id));
	}

	public function delete_bahasa($candidate_id)
	{
		$this->db->delete('alfa_t_candidate_language', array('candidate_id' => $candidate_id));
	}

	public function delete_penyakit($candidate_id)
	{
		$this->db->delete('alfa_t_candidate_disease', array('candidate_id' => $candidate_id));
	}


	public function insert_formal($alfa_educ_id, $institution_name, $major, $gpa, $end_year, $certificate, $city_id, $educ_id)
	{
		$data2['candidate_id']        = $this->session->userdata('id');
		$data2['alfa_educ_id']        = $alfa_educ_id;
		$data2['institution_name']    = $institution_name;
		$data2['major']               = $major;
		$data2['gpa']                 = $gpa;
		$data2['certificate']         = $certificate;
		$data2['end_year']            = $end_year;
		$data2['city_id']             = $city_id;
		$data2['educ_id']             = $educ_id;
		$this->db->insert('candidate_educ', $data2);
		$insert_id                   = $this->db->insert_id();
		return $insert_id;
	}

	public function insert_formal_last($candidateId, $educ_id,$alfa_educ_id, $institution_name, $major, $gpa, $end_year)
	{
		$data2['candidate_id']       	= $candidateId;
		$data2['educ_id']       		= $educ_id;
		$data2['alfa_educ_id']       	= $alfa_educ_id;
		$data2['institution_name']      = $institution_name;
		$data2['major']         		= $major;
		$data2['gpa']           		= $gpa;
		$data2['end_year']      		= $end_year;
		$this->db->insert('candidate_educ', $data2);
		$insert_id                   = $this->db->insert_id();
		return $insert_id;
	}

	public function insert_nonformal($topic, $desc, $end_year, $location)
	{
		$data2['candidate_id']      = $this->session->userdata('id');
		$data2['topic']       		= $topic;
		$data2['desc']      = $desc;
		$data2['end_year']         	= $end_year;
		$data2['location']          = $location;
		$this->db->insert('candidate_course', $data2);
		$insert_id                   = $this->db->insert_id();
		return $insert_id;
	}

	public function insert_skill($skill, $understanding)
	{
		$data2['candidate_id']      = $this->session->userdata('id');
		$data2['skill']       		= $skill;
		$data2['understanding']     = $understanding;
		$this->db->insert('candidate_skill', $data2);
		$insert_id                   = $this->db->insert_id();
		return $insert_id;
	}

	public function insert_penyakit($disease_id, $year, $time_disease)
	{
		$data2['candidate_id']      = $this->session->userdata('id');
		$data2['disease_id']       		= $disease_id;
		$data2['year']     = $year;
		$data2['time_disease']     = $time_disease;
		$this->db->insert('alfa_t_candidate_disease', $data2);
		$insert_id                   = $this->db->insert_id();
		return $insert_id;
	}

	public function insert_kontak($contact_name, $phone,$relation,$address)
	{
		$data2['candidate_id']      = $this->session->userdata('id');
		$data2['contact_name']      = $contact_name;
		$data2['phone']     		= $phone;
		$data2['relation']      	= $relation;
		$data2['address']     		= $address;
		$this->db->insert('candidate_emergency_call', $data2);
		$insert_id                   = $this->db->insert_id();
		return $insert_id;
	}

	public function insert_bahasa($language_id, $writing, $verbal)
	{
		$data2['candidate_id']      = $this->session->userdata('id');
		$data2['language_id']       		= $language_id;
		$data2['writing']     = $writing;
		$data2['verbal']     = $verbal;
		$this->db->insert('alfa_t_candidate_language', $data2);
		$insert_id                   = $this->db->insert_id();
		return $insert_id;
	}

	public function insert_keluarga($family_name, $dob, $gender, $relationship, $occupation)
	{
		$data2['candidate_id']      = $this->session->userdata('id');
		$data2['name']      	= $family_name;
		$data2['dob']     			= $dob;
		$data2['gender']       		= $gender;
		$data2['relationship_id']     	= $relationship;
		$data2['occupation']       	= $occupation;
		$this->db->insert('candidate_family_detail', $data2);
		$insert_id                   = $this->db->insert_id();
		return $insert_id;
	}

	public function insert_org($org_name, $org_member, $start_year, $end_year, $org_desc)
	{
		$data2['candidate_id']       	= $this->session->userdata('id');
		$data2['org_name']       		= $org_name;
		$data2['org_member']      		= $org_member;
		$data2['org_desc']      		= $org_desc;
		$data2['start_year']          	= $start_year;
		$data2['end_year']      		= $end_year;
		$this->db->insert('candidate_org', $data2);
		$insert_id                   = $this->db->insert_id();
		return $insert_id;
	}

	public function insert_experience($company, $position, $number_sub, $head_position, $last_drawn_salary, $reason_for_leaving, $start_year, $end_year,$untilNow,$job_desc)
	{
		$data2['candidate_id']       = $this->session->userdata('id');
		$data2['job_desc']       = $job_desc;
		$data2['company_name']       = $company;
		$data2['position']           = $position;
		$data2['start_year']         = $start_year;
		$data2['end_year']           = $end_year;
		$data2['number_sub']      	 = $number_sub;
		$data2['head_position']      = $head_position;
		$data2['last_drawn_salary']  = $last_drawn_salary;
		$data2['reason_for_leaving'] = $reason_for_leaving;
		$data2['until_now'] = $untilNow;
		$this->db->insert('candidate_exp', $data2);
		$insert_id                   = $this->db->insert_id();
		return $insert_id;
	}
}
