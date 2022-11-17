<?php
class ControllerExtensionModuleAttributeTemplates extends Controller {
	private $error = array();
	
	public function install(){
		
		$this->db->query("CREATE TABLE IF NOT EXISTS `".DB_PREFIX."attribute_templates` (
		  `template_id` int(11) PRIMARY KEY NOT NULL AUTO_INCREMENT,
		  `name` varchar(255) NOT NULL
		)");

		$this->db->query("CREATE TABLE IF NOT EXISTS `".DB_PREFIX."attribute_templates_attributes` (
		  `template_id` int(11) NOT NULL,
		  `attribute_id` int(11) NOT NULL,
		  `language_id` int(11) NOT NULL,
		  `text` text NOT NULL
		) ");
	}
	public function index() {


		$this->install();

		$this->load->language('extension/module/attributetemplates');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('setting/setting');
		$admin_language_id=$this->config->get('config_language_id');
		$this->load->model('localisation/language');
		$data['attributes']=array();
		$data['templates']=array();

		$data['languages'] = $this->model_localisation_language->getLanguages();
		$templates=$this->db->query("select * from ".DB_PREFIX."attribute_templates where 1")->rows;

		foreach($templates as $template){
			$template['href']=$this->url->link('extension/module/attributetemplates/addTemplate','template_id='.$template['template_id'].'&user_token='.$this->session->data['user_token']);
			$template['delete']=$this->url->link('extension/module/attributetemplates/delete','template_id='.$template['template_id'].'&user_token='.$this->session->data['user_token']);

			$data['templates'][]=$template;
		}
		$attributes=$this->db->query("select a.attribute_id,a.name,c.name as groupname from ".DB_PREFIX."attribute_description a left join ".DB_PREFIX."attribute b on (a.attribute_id=b.attribute_id) left join ".DB_PREFIX."attribute_group_description c on(b.attribute_group_id=c.attribute_group_id) where a.language_id='".$admin_language_id."' and c.language_id='".$admin_language_id."'")->rows;

		foreach($attributes as $attribute){
			$attribute['included']=false;
			foreach($data['languages'] as $language){
				$attribute['values'][$language['language_id']]='';
			}
			$data['attributes'][$attribute['attribute_id']]=$attribute;
		}

		if(isset($this->request->get['template_id']))
		{
			$data['template_id']=$template_id=	$this->request->get['template_id'];
			$attribute_template=$this->db->query("select * from ".DB_PREFIX."attribute_templates_attributes where  template_id='".$template_id."'")->rows;



			foreach($attribute_template as $at){
				if(isset($data['attributes'][$at['attribute_id']])){
					$data['attributes'][$at['attribute_id']]['values'][$at['language_id']]=$at['text'];
				}

				$data['attributes'][$at['attribute_id']]['included']=true;
			}

		}else
		$data['template_id']=	0;




		if(isset($this->request->get['template_id']))
		{
			$data['template_id']=	$this->request->get['template_id'];
			$data['name']=$this->db->query("select name from ".DB_PREFIX."attribute_templates where template_id='".$data['template_id']."'")->row['name'];


			$data['text_edit'] = $this->language->get('text_edit');

		}else
		{
			$data['template_id']=	0;
			$data['name']="";
			$data['text_edit'] = $this->language->get('text_add');
		}

		
		$data['heading_title'] = $this->language->get('heading_title');
		$data['text_table_column_attribute'] = $this->language->get('text_table_column_attribute');
		$data['text_table_column_attribute_value'] = $this->language->get('text_table_column_attribute_value');
		$data['text_table_column_attribute_group'] = $this->language->get('text_table_column_attribute_group');


		$data['text_enabled'] = $this->language->get('text_enabled');
		$data['text_name'] = $this->language->get('text_name');
		$data['text_disabled'] = $this->language->get('text_disabled');

		$data['entry_status'] = $this->language->get('entry_status');

		$data['button_save'] = $this->language->get('button_save');
		$data['button_cancel'] = $this->language->get('button_cancel');

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], 'SSL')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_module'),
			'href' => $this->url->link('extension/extension', 'user_token=' . $this->session->data['user_token'], 'SSL')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('extension/module/attributetemplateslist', 'user_token=' . $this->session->data['user_token'], 'SSL')
		);


		$data['add'] = $this->url->link('extension/module/attributetemplates/addTemplate', 'user_token=' . $this->session->data['user_token'], 'SSL');

		$data['action'] = $this->url->link('extension/module/attributetemplates/addTemplate', 'user_token=' . $this->session->data['user_token'], 'SSL');



		$data['cancel'] = $this->url->link('extension/extension', 'user_token=' . $this->session->data['user_token'], 'SSL');

		$data['header'] = $this->load->controller('common/header');

		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/module/attributetemplateslist', $data));
	}

	public function addTemplate() {
		if(!isset($this->request->get['template_id']))
			$data['action'] = str_replace("amp;","", $this->url->link('extension/module/attributetemplates/addTemplate', 'user_token=' . $this->session->data['user_token'], 'SSL'));
		else
			$data['action'] = str_replace("amp;","", $this->url->link('extension/module/attributetemplates/addTemplate', 'template_id='.$this->request->get['template_id'].'&user_token=' . $this->session->data['user_token'], 'SSL'));

		$data['list'] = str_replace("amp;","", $this->url->link('extension/module/attributetemplates', 'user_token=' . $this->session->data['user_token'], 'SSL'));


		$this->load->language('extension/module/attributetemplates');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('setting/setting');
		$admin_language_id=$this->config->get('config_language_id');
		$this->load->model('localisation/language');
		$data['attributes']=array();
		$data['templates']=array();

		$data['languages'] = $this->model_localisation_language->getLanguages();
		$templates=$this->db->query("select * from ".DB_PREFIX."attribute_templates where 1")->rows;

		foreach($templates as $template){
			$template['href']=$this->url->link('extension/module/attributetemplates','template_id='.$template['template_id'].'&user_token='.$this->session->data['user_token']);
			$template['delete']=$this->url->link('extension/module/attributetemplates/delete','template_id='.$template['template_id'].'&user_token='.$this->session->data['user_token']);

			$data['templates'][]=$template;

			
		}
		$attributes=$this->db->query("select a.attribute_id,a.name,c.name as groupname from ".DB_PREFIX."attribute_description a left join ".DB_PREFIX."attribute b on (a.attribute_id=b.attribute_id) left join ".DB_PREFIX."attribute_group_description c on(b.attribute_group_id=c.attribute_group_id) where a.language_id='".$admin_language_id."' and c.language_id='".$admin_language_id."'")->rows;



		foreach($attributes as $attribute){
			$attribute['included']=false;
			foreach($data['languages'] as $language){
				$attribute['values'][$language['language_id']]='';
			}
			$data['attributes'][$attribute['attribute_id']]=$attribute;


		}



		if(isset($this->request->get['template_id']))
		{
			$data['template_id']=$template_id=	$this->request->get['template_id'];
			$attribute_template=$this->db->query("select * from ".DB_PREFIX."attribute_templates_attributes where  template_id='".$template_id."'")->rows;


			foreach($attribute_template as $at){
				if(isset($data['attributes'][$at['attribute_id']])){
					$data['attributes'][$at['attribute_id']]['values'][$at['language_id']]=$at['text'];
				}

				$data['attributes'][$at['attribute_id']]['included']=true;


			}

		}else
		$data['template_id']=	0;




		if(isset($this->request->get['template_id']))
		{
			$data['template_id']=	$this->request->get['template_id'];
			$data['name']=$this->db->query("select name from ".DB_PREFIX."attribute_templates where template_id='".$data['template_id']."'")->row['name'];

			$data['text_edit'] = $this->language->get('text_edit');

		}else
		{
			$data['template_id']=	0;
			$data['name']="";
			$data['text_edit'] = $this->language->get('text_add');
		}

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {

					
					$template=$this->request->post['new'];
					if(!$template['name'])
					{
					$template['name']="Untitled New Template";
					}
					if($this->request->get['template_id'])
					{
						$template_id=$this->request->get['template_id'];
						$this->db->query("update ".DB_PREFIX."attribute_templates set name='".$template['name']."' where template_id='".$template_id."'");
						$this->db->query("delete from ".DB_PREFIX."attribute_templates_attributes where template_id='".$template_id."'");


					}else {
						$this->db->query("insert into ".DB_PREFIX."attribute_templates set name='".$template['name']."'");
						$template_id=$this->db->getLastId();
					}

					foreach($template['attributes'] as $attribute_id=>$newattr)
					{
					if(isset($newattr['include']))
					{

						foreach($newattr['values'] as $language_id=>$val)
						{

							$this->db->query("insert into ".DB_PREFIX."attribute_templates_attributes set template_id='".$template_id."',language_id='".$language_id."',attribute_id='".$attribute_id."',text='".$val."'");
						}
					}
					}





			$this->session->data['success'] = $this->language->get('text_success');

			$this->response->redirect($this->url->link('extension/module/attributetemplates', 'user_token=' . $this->session->data['user_token'], 'SSL'));
		}

		$data['heading_title'] = $this->language->get('heading_title');
		$data['text_table_column_attribute'] = $this->language->get('text_table_column_attribute');
		$data['text_table_column_attribute_value'] = $this->language->get('text_table_column_attribute_value');
		$data['text_table_column_attribute_group'] = $this->language->get('text_table_column_attribute_group');


		$data['text_enabled'] = $this->language->get('text_enabled');
		$data['text_name'] = $this->language->get('text_name');
		$data['text_disabled'] = $this->language->get('text_disabled');

		$data['entry_status'] = $this->language->get('entry_status');

		$data['button_save'] = $this->language->get('button_save');
		$data['button_cancel'] = $this->language->get('button_cancel');

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], 'SSL')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_module'),
			'href' => $this->url->link('extension/extension', 'user_token=' . $this->session->data['user_token'], 'SSL')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('extension/module/attributetemplates', 'user_token=' . $this->session->data['user_token'], 'SSL')
		);



		$data['cancel'] = $this->url->link('extension/extension', 'user_token=' . $this->session->data['user_token'], 'SSL');

	
		$data['header'] = $this->load->controller('common/header');

		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/module/attributetemplates', $data));

	}

	public function delete(){
		$this->db->query("delete from ".DB_PREFIX."attribute_templates where template_id=".$this->request->get['template_id']);
		$this->db->query("delete from ".DB_PREFIX."attribute_templates_attributes where template_id=".$this->request->get['template_id']);
			$this->response->redirect($this->url->link('extension/module/attributetemplates', 'user_token=' . $this->session->data['user_token'], 'SSL'));
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'extension/module/attributetemplates')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return !$this->error;
	}
}
