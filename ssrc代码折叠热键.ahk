SetTitleMatchMode, RegEx

Return

#IfWinActive .*\(ssrc\) - Sublime Text
	^e::
		Clipboard := "{//_"
	    Send ^f^v!{Enter}^+m^+[
	    Return
	F12:: ExitApp