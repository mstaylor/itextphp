#include "itextPHP.h"
#include <wchar.h>

#include "itextphpString.h"

/* {{{ proto resource itextphp_wingdings_chartobyte(resource characterArray, string encoding)
   convert a string to a byte array */
PHP_FUNCTION(itextphp_symbolttconversion_chartobyte)
{
    
    
      /* the original itext java blurb */
    
      /**char ch[] = text.toCharArray();
      byte b[] = new byte[ch.length];
      int ptr = 0;
      int len = ch.length;
      for (int k = 0; k < len; ++k) {
          char c = ch[k];
          if ((c & 0xff00) == 0 || (c & 0xff00) == 0xf000)
              b[ptr++] = (byte)c;
      }
      if (ptr == len)
          return b;
      byte b2[] = new byte[ptr];
      System.arraycopy(b, 0, b2, 0, ptr);
      return b2;**/
    
    zval * characterArray, * encoding;    
    
    byte * aByte;
    unsigned char * theByte;
    int k = 0;
    itextstring *theString;
    switch(ZEND_NUM_ARGS())
    {
    
        case 2:
	{
	    if(zend_parse_parameters(2,"rs", &characterArray, &encoding)==FAILURE){
	        RETURN_FALSE;
	    }
	    else
	    {
		//get the string resource
		ZEND_FETCH_RESOURCE(theString, itextstring *, &characterArray, -1, le_itextphpStringResourceName, le_itextphpStringResource);
		
		
		aByte = (byte*) emalloc(sizeof(byte));
		aByte->theByte = (unsigned char *)emalloc(sizeof(unsigned char)*theString->length);
		aByte->size = theString->length;
		theByte = aByte->theByte;
		
		int ptr = 0;
		
                for (k = 0; k < theString->length;k++)
		{
		     if (theString == NULL)
		    {
			RETURN_FALSE;
		    }
		    else
		    {
			wchar_t c;
		        if (theString->isWide == NOTTA)
			{
			    //theByte[k] = wctob(btowc(theString->dataChar[k]));
			    // char c = cc[k];
			    c = btowc(theString->dataChar[k]); 
		        }
			else
			{
			    //theByte[k] = wctob(theString->dataWide[k]);
			    // char c = cc[k];
			    c = theString->dataWide[k];
		        }
			
			if ((c & 0xff00) == 0 || (c & 0xff00) == 0xf000)
			{
			     aByte->theByte[ptr++] = (unsigned char)wctob(c);
			}
		    }
	        }
		
		if (ptr == theString->length)
	           ZEND_REGISTER_RESOURCE(return_value, aByte ,le_itextphpByteResource);
		
		byte* b2 = (byte*) emalloc(sizeof(byte));
		b2->theByte = (unsigned char *)emalloc(sizeof(unsigned char)*ptr);
		b2->size = ptr;
		
		for (k=0; k < aByte->size; k++)
		{
		    b2->theByte[k] = aByte->theByte[k];
		}
		
		ZEND_REGISTER_RESOURCE(return_value, b2 ,le_itextphpByteResource);
	    }
        }
    }
}

