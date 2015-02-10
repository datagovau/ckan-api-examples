using System.Collections.Specialized;
using System.Configuration;
namespace DataGovAuUpload {
   public static class WebConfigSettings {
      //-------------------------------------------------------------------
      public static string DataGovApiKey {
         get {
            return GetApplicationSetting("datagov.api.key");
         }
      }
      //-------------------------------------------------------------------
      public static string DataGovCkanUpdateUrl {
         get {
            return GetApplicationSetting("datagov.ckanupdate.url");
         }
      }
      //-------------------------------------------------------------------
      public static string DataGovResourceId {
         get {
            return GetApplicationSetting("datagov.resource.id");
         }
      }
            //-------------------------------------------------------------------
      public static int TimeoutMilliseconds {
         get {
            try {
               return int.Parse( GetApplicationSetting("Timeout.Milliseconds"));
            }
            catch {
               return 50000;
            }
         }
      }
      //-------------------------------------------------------------------
      public static string GetApplicationSetting(string name) {
         NameValueCollection AppSettings = ConfigurationManager.AppSettings;
         try {
            return AppSettings.Get(name);
         }
         catch {
            return "";
         }
      }
      
   }
}