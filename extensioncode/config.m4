PHP_ARG_ENABLE(iTextPHP, whether to enable iTextPHP support,
[ --enable-iTextPHP   Enable iTextPHP support])

if test "$PHP_ITEXTPHP" = "yes"; then
  AC_DEFINE(HAVE_ITEXTPHP, 1, [Whether you have iTextPHP])
  PHP_NEW_EXTENSION(iTextPHP, iTextPHP.c byte.c iTextPHPString.c wingdingsConversion.c cp437conversion.c symbolconversion.c pdfencodings.c, $ext_shared)
fi
