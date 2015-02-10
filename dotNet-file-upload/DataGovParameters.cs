using System.Runtime.Serialization;
using System.Collections.Specialized;
namespace DataGovAuUpload {
   [DataContract]
   class DataGovParameters {
      public const string MimeType = "text/csv";
      [DataMember()]
      public string ApiKey { get; set; }
      [DataMember()]
      public string CkanUrlUpdate { get; set; }
      [DataMember()]
      public string Filename { get; set; }

      public NameValueCollection NameValueCollection { get; set; }
      public DataGovParameters() {
         this.NameValueCollection = new NameValueCollection();
      }
   }
}
