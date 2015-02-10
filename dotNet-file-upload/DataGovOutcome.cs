using System.Runtime.Serialization;
namespace DataGovAuUpload {
   [DataContract]
   class DataGovOutcome {
      [DataMember()]
      public string PostStatus { get; set; }
      [DataMember()]
      public bool Success { get; set; }
      [DataMember()]
      public string FormattedMessage { get; set; }
   }
}
