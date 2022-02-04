; kernel and basic reset code from http://codebase64.org/doku.php?id=base:assembling_your_own_cart_rom_image
; compile command line: dasm loader.asm -f3 -oloader.bin
; DASM: http://dasm-dillon.sourceforge.net

		processor 6502
		org $8000
		dc.w start
		dc.w start
		hex c3 c2 cd 38 30  ; cbm80

bank = $57
size = $58
src = $5a
dst = $2d

start
		; KERNAL RESET ROUTINE
		sei
		stx $d016		; Turn on VIC for PAL / NTSC check
		jsr $fda3		; IOINIT - Init CIA chips
		jsr $fd50		; RANTAM - Clear/test system RAM
		lda #$a0
		sta $0284		; ignore cartridge ROM for end of detected RAM for BASIC
		jsr $fd15		; RESTOR - Init KERNAL RAM vectors
		jsr $ff5b		; CINT   - Init VIC and screen editor
		cli			; Re-enable IRQ interrupts

		; BASIC RESET  Routine
		jsr $e453		; Init BASIC RAM vectors
		jsr $e3bf		; Main BASIC RAM Init routine
		jsr $e422		; Power-up message / NEW command
		ldx #$fb
		txs			; Reduce stack pointer for BASIC

		; init variables for ROM copy
		lda #0
		sta bank

		lda end
		sta size
		lda end + 1
		sta size + 1

		lda end + 2
		sta dst
		lda end + 3
		sta dst + 1

		lda #<(end + 4)
		sta src
		lda #>(end + 4)
		sta src + 1
		
		; copy launcher to screen buffer		
		ldx #0
0$		lda launcher,x
		sta $0600,x
		inx
		bne 0$
		jmp $0600

launcher
		rorg $0600

		; copy PRG from ROM
		sei
		ldx #0
0$		lda bank
		sta $de00
1$		lda (src),x
		sta (dst),x
		inc src
		bne 2$
		inc src + 1
		lda src + 1
		cmp #$a0
		bne 2$
		lda #$80
		sta src + 1
		inc bank
		lda bank
		sta $de00
2$		inc dst
		bne 3$
		inc dst + 1
3$		dec size
		bne 1$
		dec size + 1
		lda size + 1
		cmp #$ff
		bne 1$
		
		; disable cartridge ROM
		lda #$80
		sta $de00
		
		; CLR
		jsr $A663
		
		; jump to basic RUN command
		cli
		jmp $a7ae

		rend

end
