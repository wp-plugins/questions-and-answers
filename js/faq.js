$j=jQuery.noConflict();
$j(document).ready(function (){
        
      $j('.faq_q').click(function(e) {
          
        $j(this).parent().find('p').slideToggle(90); 
        e.preventDefault();
        
      });
        
});