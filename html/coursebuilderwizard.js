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
 * @copyright  Winans Creative 2011, Helmut SchottmÙller 2009
 * @author     Blair Winans <blair@winanscreative.com>
 * @author     Fred Bliss <fred.bliss@intelligentspark.com>
 * @author     Adam Fisher <adam@winanscreative.com>
 * @author     Includes code from survey_ce module from Helmut SchottmÙller <typolight@aurealis.de>
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 */
 
var CourseBuilderWizard =
{

	/**
	 * Course element wizard
	 * @param object
	 * @param string
	 * @param string
	 */
	coursebuilderWizard: function(el, command, id)
	{
		var table = $(id);
		var tbody = table.getFirst().getNext();
		var parent = $(el).getParent('tr');
		var rows = tbody.getChildren();

		Backend.getScrollOffset();

		switch (command)
		{
			case 'copy':
				var tr = new Element('tr');
				var childs = parent.getChildren();

				for (var i=0; i<childs.length; i++)
				{
					var next = childs[i].clone(true).injectInside(tr);
					next.getFirst().value = childs[i].getFirst().value;
				}

				tr.injectAfter(parent);
				break;

			case 'up':
				parent.getPrevious() ? parent.injectBefore(parent.getPrevious()) : parent.injectInside(tbody);
				break;

			case 'down':
				parent.getNext() ? parent.injectAfter(parent.getNext()) : parent.injectBefore(tbody.getFirst());
				break;

			case 'delete':
				(rows.length > 1) ? parent.destroy() : null;
				break;
		}

		rows = tbody.getChildren();

		for (var i=0; i<rows.length; i++)
		{
			var childs = rows[i].getChildren();

			for (var j=0; j<childs.length; j++)
			{
				var first = childs[j].getFirst();

				if (first.type == 'select-one')
				{
					first.name = first.name.replace(/\[[0-9]+\]/ig, '[' + i + ']');
				}
			}
		}
	},
	
	/**
	 * Swap the element type
	 * @param object
	 * @param string
	 */
	swapType: function(el, id)
	{
		var parent = $(el).getParent('tr');
		var cols = parent.getChildren();
		var childs = $(el).getChildren('option');
		for (var i=0; i<childs.length; i++)
		{
			if(childs[i].value == el.value)
			{
				var className = childs[i].get('class');
			}
		}
		if(className)
		{
			cols[1].getElement('select').value = className;
		}
		
	}


};
