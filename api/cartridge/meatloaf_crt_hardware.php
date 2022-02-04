<?php
$hw_type_array = array(
						"NORMAL CARTRIDGE",
						"ACTION REPLAY",
						"KCS POWER CARTRIDGE",
						"FINAL CARTRIDGE III",
						"SIMONS' BASIC",
						"OCEAN TYPE 1*",
						"EXPERT CARTRIDGE",
						"FUN PLAY, POWER PLAY",
						"SUPER GAMES",
						"ATOMIC POWER",
						"EPYX FASTLOAD",
						"WESTERMANN LEARNING",
						"REX UTILITY",
						"FINAL CARTRIDGE I",
						"MAGIC FORMEL",
						"C64 GAME SYSTEM, SYSTEM 3",
						"WARP SPEED",
						"DINAMIC**",
						"ZAXXON, SUPER ZAXXON (SEGA)",
						"MAGIC DESK, DOMARK, HES AUSTRALIA",
						"SUPER SNAPSHOT V5",
						"COMA-80",
						"STRUCTURED BASIC",
						"ROSS",
						"DELA EP64",
						"DELA EP7X8",
						"DELA EP256",
						"REX EP256",
						"MIKRO ASSEMBLER",
						"FINAL CARTRIDGE PLUS",
						"ACTION REPLAY 4",
						"STARDOS",
						"EASYFLASH",
						"EASYFLASH XBANK",
						"CAPTURE",
						"ACTION REPLAY 3",
						"RETRO REPLAY",
						"MMC64",
						"MMC REPLAY",
						"IDE64",
						"SUPER SNAPSHOT V4",
						"IEE-488",
						"GAME KILLER",
						"PROPHET64",
						"EXOS",
						"FREEZE FRAME",
						"FREEZE MACHINE",
						"SNAPSHOT64",
						"SUPER EXPLODE V5.0",
						"MAGIC VOICE",
						"ACTION REPLAY 2",
						"MACH 5",
						"DIASHO-MAKER",
						"PAGEFOX",
						"KINGSOFT",
						"SILVERROCK 128K CARTRIDGE",
						"FORMEL 64",
						"RGCD",
						"RR-NET MK3",
						"EASYCALC",
						"GMOD2",
						"MAX BASIC",
						"GMOD3",
						"ZIPP-CODE 48",
						"BLACKBOX V8",
						"BLACKBOX V3",
						"BLACKBOX V4",
						"REX RAM-FLOPPY",
						"BIS-PLUS",
						"SD-BOX",
						"MULTIMAX",
						"BLACKBOX V9",
						"LT. KERNAL HOST ADAPTOR",
						"RAMLINK",
						"H.E.R.O."
					);

require_once("meatloaf_crt_normal.php");
require_once("meatloaf_crt_easyflash.php");


function selectCart($hw_type)
{
	switch ( $hw_type )
	{
		case "EASYFLASH":
			return new MeatloafCRTEasyFlash();
		
		default:
			return new MeatloafCRTNormal();
	}	
}