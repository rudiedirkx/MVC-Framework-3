/**
 * Reordering library
 * 
 * CHANGELOG
 * =================
 * DATE				VERSION			AUTHOR					DESCRIPTION
 * 2006-09-10		1.0.1			Rudie Dirkx				- Initial release
 * 2006-09-13		1.0.2			Rudie Dirkx				- Replaced eval() with [''+]
 *															- Added function G(), to replace $() (which is not in this library)
 *															- Updated function mf_SelectRow():
 *																- Original background color is now restored
 *																- More efficient way of selecting (removed a piece of identical code)
 * 2006-09-13		1.0.3			Rudie Dirkx				- More efficient way of switching rows (no more cloning and only two objects needed)
 * 2009-05-07		1.0.4			Rudie Dirkx				- Also possible to change className of hilited row (make 2nd param object instead of string)
 * 
 * TODO
 * =================
 * - Dynamic UP and DOWN action buttons -> in the same row as the selected row (when none selected, hide buttons)
 */

var Reorder = function( f_objTable, f_szHiliteBackgroundColor, f_szHtmlIdPrefix ) {
	this.m_objTable = this.G(f_objTable);
	if ( 'string' == typeof f_szHiliteBackgroundColor ) {
		this.m_szHiliteBackgroundColor = f_szHiliteBackgroundColor;
	}
	else if ( 'object' == typeof f_szHiliteBackgroundColor ) {
		if ( f_szHiliteBackgroundColor.class ) {
			this.m_szHiliteClassName = f_szHiliteBackgroundColor.class;
		}
		if ( f_szHiliteBackgroundColor.bgcolor ) {
			this.m_szHiliteBackgroundColor = f_szHiliteBackgroundColor.bgcolor;
		}
	}
	this.m_szHtmlIdPrefix = f_szHtmlIdPrefix;
};

Reorder.version = '1.0.4';
Reorder.prototype = {
	m_objTable						: null,
	m_szHtmlIdPrefix				: '',
	m_szSelectedId					: '',

	m_szHiliteClassName				: '',
	m_szHiliteBackgroundColor		: '',
	m_szCurrentRowBackgroundColor	: '',

	m_bReorderTableIds				: false,
	m_iReorderTableColumnIndex		: 0,
	m_iReorderTableStartsAt			: 0,

	m_szOrderListPrefix				: '',
	m_szOrderListGlue				: ',',


	G : function(id) {
		if ( 'object' != typeof id ) {
			id = document.getElementById(id);
		}
		return id;
	},

	mf_SelectRow : function( f_szRowId ) {
		var objRow = this.G(f_szRowId);
		// If another row was hilited
		if ( this.m_szSelectedId && this.G(this.m_szSelectedId) ) {
			// Unhilite that one
			this.G(this.m_szSelectedId).className = this.G(this.m_szSelectedId).className.replace(this.m_szHiliteClassName, ''); // class
			this.G(this.m_szSelectedId).style.backgroundColor = this.m_szCurrentRowBackgroundColor; // bgcolor
			// If that's the one that was clicked on
			if ( f_szRowId == this.m_szSelectedId ) {
				// Remove selection in all
				this.m_szSelectedId = '';
				return false;
			}
		}
		// Select new row
		this.m_szCurrentRowBackgroundColor = objRow.style.backgroundColor; // current bgcolor
		objRow.style.backgroundColor = this.m_szHiliteBackgroundColor; // bgcolor
		objRow.className += ' '+this.m_szHiliteClassName; // class
		this.m_szSelectedId = f_szRowId;
		return false;
	},

	mf_MoveSelectedUp : function() {
		return this.mf_MoveSelect(-1);
	},

	mf_MoveSelectedDown : function() {
		return this.mf_MoveSelect(1);
	},

	mf_MoveSelect : function( f_iMotion, f_iLimit ) {
		if ( !this.m_szSelectedId || !this.G(this.m_szSelectedId) ) {
			return false;
		}
		var r = this.G(this.m_szSelectedId);
		var t = this.m_objTable;
		// Down
		if ( 1 == f_iMotion ) {
			// Move one down
			if ( t.rows.length != r.sectionRowIndex+1 ) {
				t.insertBefore(t.rows[r.sectionRowIndex+1], r);
			}
			// Move to top
			else {
				t.insertBefore(r, t.rows[0]);
			}
		}
		// Up
		else if ( -1 == f_iMotion ) {
			// Move one up
			if ( 0 != r.sectionRowIndex ) {
				t.insertBefore(r, t.rows[r.sectionRowIndex-1]);
			}
			// Move to bottom
			else {
				t.appendChild(r);
			}
		}
		if ( 1 == f_iMotion*f_iMotion && this.m_bReorderTableIds )
		{
			r = this.m_objTable.rows;
			for ( i=0; i<r.length; i++ ) {
				r[i].cells[this.m_iReorderTableColumnIndex].innerHTML = "" + (i+this.m_iReorderTableStartsAt) + "";
			}
		}
		return false;
	},

	mf_GetOrderList : function() {
		var rows = this.m_objTable.rows, l = this.m_szHtmlIdPrefix.length, szList = '';
		for ( i=0; i<rows.length; i++ ) {
			szList += this.m_szOrderListGlue + rows[i].id.substr(l);
		}
		return this.m_szOrderListPrefix + szList.substr(this.m_szOrderListGlue.length);
	}

};
