#!/bin/bash
/usr/bin/ftp -d trackula.me << ftpEOF
	prompt
	put "*.html"
	quit
ftpEOF