<?php
// Meatloaf Server
// https://github.com/idolpx/meatloaf-svr
// Copyright(C) 2020 James Johnston
//
// Meatloaf is free software : you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
// 
// Meatloaf is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU General Public License for more details.
// 
// You should have received a copy of the GNU General Public License
// along with Meatloaf. If not, see <http://www.gnu.org/licenses/>.

function ascii2petscii( $c )
{
	if ($c > 64 && $c < 91) $c += 128;
	else if ($c > 96 && $c < 123) $c -= 32;
	else if ($c > 192 && $c < 219) $c -= 128;
	else if ($c == 95) $c = 164; // to handle underscore 
	return $c;
}

function petscii2ascii( $c )
{
	if ($c >(64 + 128) && $c < (91 + 128)) $c -= 128;
	else if ($c >(96 - 32) && $c < (123 - 32)) $c += 32;
	else if ($c >(192 - 128) && $c < (219 - 128)) $c += 128;
	else if ($c == 164) $c = 95; // to handle underscore 
	return $c;
}

function petscii2screen( $c )
{
	if (($c >= 0x40 && $c <= 0x5F) || ($c >= 0xa0 && $c <= 0xbf)) $c -= 0x40;
	else if ($c >= 0xc0 && $c <= 0xdf) $c -= 0x80;
	else if ($c >= 0 && $c <= 0x1f) $c += 0x80;
	else if (($c >= 0x60 && $c <= 0x7F) || ($c >= 0x90 && $c <= 0x9f)) $c += 0x40;
	return $c;
}

function screen2petscii( $c )
{
	if (($c >= 0 && $c <= 0x1F) || ($c >= 0x60 && $c <= 0x7f)) $c += 0x40;
	else if ($c >= 0x40 && $c <= 0x5f) $c += 0x80;
	else if ($c >= 0x80 && $c <= 0x9f) $c -= 0x80;
	else if (($c >= 0xa0 && $c <= 0xbF) || ($c >= 0xd0 && $c <= 0xdf)) $c -= 0x40;
	return $c;
}

function a2p_str( $string )
{
	for ( $i=0; $i < strlen( $string ); $i++ )
	{
		$string[$i] = ascii2petscii( $string[$i] );
	}
	return $string;
}

function p2a_str( $string )
{
	echo $string." - before\r\n";
	for ( $i=0; $i < strlen( $string ); $i++ )
	{
		$string[$i] = petscii2screen( $string[$i] );
	}
	echo $string." - after"; exit();
	return $string;
}

?>