#include "itextPHP.h"

/* {{{ proto  itextphp_md5INIT()
   return a md5resource */
PHP_FUNCTION(itextphp_md5INIT)
{
   
    md5Resource * md5;
    md5 = (md5Resource*) emalloc(sizeof(md5Resource));
    PHP_MD5Init(&md5->md5ctx);
    ZEND_REGISTER_RESOURCE(return_value, md5 ,le_itextphpMD5Resource);
	    
	  
    
}


/* {{{ proto  itextphp_md5UPDATE(resource md5, resource byte)
   update the md5 */
PHP_FUNCTION(itextphp_md5UPDATE)
{
    zval *md5, * offset, * length;
    zval *abyte;
    byte * aByte;
    md5Resource * aMD5;
    int Length = 0;
    switch(ZEND_NUM_ARGS())
    {
	
	case 2:
	{
	    if (zend_parse_parameters(2, "rr", &md5, &abyte) != FAILURE)
            {
	        ZEND_FETCH_RESOURCE(aByte, byte *, &abyte, -1, le_itextphpanotherByteResourceName, le_itextphpanotherByteResource);
		ZEND_FETCH_RESOURCE(aMD5, md5Resource *, &md5, -1, le_itextphpMD5ResourceName, le_itextphpMD5Resource);
		if (aByte!= NULL && aMD5 != NULL)
		{
		   
		   PHP_MD5Update(&aMD5->md5ctx, aByte->theByte, aByte->size);
		   ZEND_REGISTER_RESOURCE(return_value, aMD5 ,le_itextphpMD5Resource);

		}
	    }
	    
	    
	    break;
	}
	case 4:
	{
	    if (zend_parse_parameters(4, "rrll", &md5, &abyte, &offset, &length) != FAILURE)
            {
	        ZEND_FETCH_RESOURCE(aByte, byte *, &abyte, -1, le_itextphpanotherByteResourceName, le_itextphpanotherByteResource);
		ZEND_FETCH_RESOURCE(aMD5, md5Resource *, &md5, -1, le_itextphpMD5ResourceName, le_itextphpMD5Resource);
		if (aByte!= NULL && aMD5 != NULL)
		{
		   Length = Z_LVAL_P(length);
		   PHP_MD5Update(&aMD5->md5ctx, aByte->theByte, Length);
		   ZEND_REGISTER_RESOURCE(return_value, aMD5 ,le_itextphpMD5Resource);

		}
	    }
	    break;
	}
	
	
    }
}


/* {{{ proto  itextphp_md5UPDATE(resource md5, resource byte)
   return a new byte digested */
PHP_FUNCTION(itextphp_md5DIGEST)
{
    zval *md5;
    zval *abyte;
    byte * aByte, * bByte;
    md5Resource * aMD5;
    switch(ZEND_NUM_ARGS())
    {
	case 1:
	{
	    if (zend_parse_parameters(1, "r", &md5) != FAILURE)
            {
	         ZEND_FETCH_RESOURCE(aByte, byte *, &abyte, -1, le_itextphpanotherByteResourceName, le_itextphpanotherByteResource);
	        if (aByte!= NULL )
		{
	            unsigned char hash[16];
		    PHP_MD5Final(hash, &aMD5->md5ctx);
		    bByte = (byte*) emalloc(sizeof(byte));
		    bByte->theByte = (unsigned char *)emalloc(sizeof(unsigned char)*16);
		    bByte->size = 16;
		    bByte->pointer = 0;
		    make_digest(aByte->theByte, hash);
		    ZEND_REGISTER_RESOURCE(return_value, bByte ,le_itextphpanotherByteResource);
		}
	    }
	    break;
	}
	
	case 2:
	{
	    if (zend_parse_parameters(2, "rr", &md5, &abyte) != FAILURE)
            {
	        ZEND_FETCH_RESOURCE(aByte, byte *, &abyte, -1, le_itextphpanotherByteResourceName, le_itextphpanotherByteResource);
		ZEND_FETCH_RESOURCE(aMD5, md5Resource *, &md5, -1, le_itextphpMD5ResourceName, le_itextphpMD5Resource);
		if (aByte!= NULL && aMD5 != NULL)
		{
		   unsigned char hash[16];
		   PHP_MD5Update(&aMD5->md5ctx, aByte->theByte, aByte->size);
		   PHP_MD5Final(hash, &aMD5->md5ctx);
		   make_digest(aByte->theByte, hash);
		   //now create a new resource
		   bByte = (byte*) emalloc(sizeof(byte));
		   bByte->theByte = (unsigned char *)emalloc(sizeof(unsigned char)*16);
		   bByte->size = 16;
		   bByte->pointer = 0;
		   memcpy(bByte->theByte, aByte->theByte, 16);
		   ZEND_REGISTER_RESOURCE(return_value, bByte ,le_itextphpanotherByteResource);

		}
	    }
	    
	    
	    break;
	}
	
	
	
    }
}







