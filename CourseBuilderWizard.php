<?php if (!defined('TL_ROOT')) die('You can not access this file directly!');

/**
 * Contao Open Source CMS
 * Copyright (C) 2005-2011 Leo Feyer
 *
 * This program is free software: you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation, either
 * version 3 of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this program. If not, please visit the Free
 * Software Foundation website at <http://www.gnu.org/licenses/>.
 *
 * PHP version 5
 * @copyright  Winans Creative 2011, Helmut Schottmüller 2009
 * @author     Blair Winans <blair@winanscreative.com>
 * @author     Fred Bliss <fred.bliss@intelligentspark.com>
 * @author     Adam Fisher <adam@winanscreative.com>
 * @author     Includes code from survey_ce module from Helmut Schottmüller <typolight@aurealis.de>
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 */


/**
 * Class CourseBuilderWizard
 *
 * Provide methods to handle courses and assemble individual pieces.
 * @copyright  Winans Creative 2011
 * @author     Blair Winans <http://www.contao.org>
 * @package    Controller
 */
class CourseBuilderWizard extends Widget
{

	/**
	 * Submit user input
	 * @var boolean
	 */
	protected $blnSubmitInput = false;

	/**
	 * Template
	 * @var string
	 */
	protected $strTemplate = 'be_widget';


	/**
	 * Add specific attributes
	 * @param string
	 * @param mixed
	 */
	public function __set($strKey, $varValue)
	{
		switch ($strKey)
		{
			case 'value':
				$this->varValue = deserialize($varValue);
				break;

			case 'mandatory':
				$this->arrConfiguration['mandatory'] = $varValue ? true : false;
				break;

			default:
				parent::__set($strKey, $varValue);
				break;
		}
	}


	/**
	 * Generate the widget and return it as string
	 * @return string
	 */
	public function generate()
	{
		$GLOBALS['TL_JAVASCRIPT'][] = 'system/modules/coursebuilderwizard/html/coursebuilderwizard.js';
		$GLOBALS['TL_CSS'][] = 'system/modules/coursebuilderwizard/html/coursebuilderwizard.css';
	
	
		$this->import('Database');

		$arrButtons = array('copy', 'up', 'down', 'delete');
		$strCommand = 'cmd_' . $this->strField;

		// Change the order
		if ($this->Input->get($strCommand) && is_numeric($this->Input->get('cid')) && $this->Input->get('id') == $this->currentRecord)
		{
			switch ($this->Input->get($strCommand))
			{
				case 'copy':
					$this->varValue = array_duplicate($this->varValue, $this->Input->get('cid'));
					break;

				case 'up':
					$this->varValue = array_move_up($this->varValue, $this->Input->get('cid'));
					break;

				case 'down':
					$this->varValue = array_move_down($this->varValue, $this->Input->get('cid'));
					break;

				case 'delete':
					$this->varValue = array_delete($this->varValue, $this->Input->get('cid'));
					break;
			}
		}
		
		$elements = array();
		
		// Get all available options to assemble the courses
		foreach($GLOBALS['CB_ELEMENT'] as $strClass=>$arrData)
		{
			$objElements = $this->Database->prepare("SELECT id, name FROM {$arrData['table']} ORDER BY name")->execute();
			
			if ($objElements->numRows)
			{
				while($objElements->next())
				{
					$arrEl = $objElements->row(); 
					$arrEl['type'] = $strClass;
					$elements[] = $arrEl;
				}
			
			}			
		}
		
		// Get new value
		if ($this->Input->post('FORM_SUBMIT') == $this->strTable)
		{
			$this->varValue = $this->Input->post($this->strId);
		}

		// Make sure there is at least an empty array
		if (!is_array($this->varValue) || !$this->varValue[0])
		{
			$this->varValue = array('');
		}

		// Save the value
		if ($this->Input->get($strCommand) || $this->Input->post('FORM_SUBMIT') == $this->strTable)
		{
			$this->Database->prepare("UPDATE " . $this->strTable . " SET " . $this->strField . "=? WHERE id=?")
						   ->execute(serialize($this->varValue), $this->currentRecord);

			// Reload the page
			if (is_numeric($this->Input->get('cid')) && $this->Input->get('id') == $this->currentRecord)
			{
				$this->redirect(preg_replace('/&(amp;)?cid=[^&]*/i', '', preg_replace('/&(amp;)?' . preg_quote($strCommand, '/') . '=[^&]*/i', '', $this->Environment->request)));
			}
		}

		// Add label and return wizard
		$return .= '<table cellspacing="0" cellpadding="0" id="ctrl_'.$this->strId.'" class="tl_courseBuilderWizard" summary="Course wizard">
  <thead>
  <tr>
    <td>'.$GLOBALS['TL_LANG']['MSC']['cb_course'].'</td>
    <td>&nbsp;</td>
  </tr>
  </thead>
  <tbody>';

		// Load tl_article language file
		$this->loadLanguageFile('tl_article');

		// Add input fields
		for ($i=0; $i<count($this->varValue); $i++)
		{
			$options = '';

			// Add modules
			foreach ($elements as $v)
			{
				$options .= '<option value="'.specialchars($v['type']).'|'.specialchars($v['id']).'"'.$this->optionSelected($v['type'].'|'.$v['id'], $this->varValue[$i]).'>'.$v['name'].'</option>';
			}

			$return .= '
  <tr>
    <td><select name="'.$this->strId.'['.$i.']" class="tl_select" onfocus="Backend.getScrollOffset();">'.$options.'</select></td>';
			
			$return .= '<td>';

			foreach ($arrButtons as $button)
			{
				$return .= '<a href="'.$this->addToUrl('&amp;'.$strCommand.'='.$button.'&amp;cid='.$i.'&amp;id='.$this->currentRecord).'" title="'.specialchars($GLOBALS['TL_LANG']['MSC']['mw_'.$button]).'" onclick="CourseBuilderWizard.coursebuilderWizard(this, \''.$button.'\',  \'ctrl_'.$this->strId.'\'); return false;">'.$this->generateImage($button.'.gif', $GLOBALS['TL_LANG']['MSC']['mw_'.$button], 'class="tl_listwizard_img"').'</a> ';
			}

			$return .= '</td>
  </tr>';
		}

		return $return.'
  </tbody>
  </table>';
	}
}

?>