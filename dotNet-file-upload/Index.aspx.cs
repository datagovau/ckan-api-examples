using System;
using System.Collections.Generic;
using System.Linq;
using System.Web;
using System.Web.UI;
using System.Web.UI.WebControls;

namespace DataGovAuUpload {
   public partial class Index : System.Web.UI.Page {
      protected void Page_Load(object sender, EventArgs e) {
         this.SubmitButton.Click += new EventHandler(this.SubmitButton_Click);
         this.SubmitButton.Attributes.Add("onclick", "document.body.style.cursor = 'wait';");
         if (!IsPostBack) {
            InitialiseForm();
         }
      }
      //---------------------------------------------------------------------------------------
      private void InitialiseForm() {
         this.TextBoxApiKey.Text = WebConfigSettings.DataGovApiKey;
         this.TextBoxResourceId.Text = WebConfigSettings.DataGovResourceId;
      }
      //---------------------------------------------------------------------------------------
      private DataGovParameters SetParameters() {
         const string IdKey = "id";
         DataGovParameters Parameters = new DataGovParameters();
         Parameters.ApiKey = this.TextBoxApiKey.Text;
         Parameters.Filename = this.TextBoxFileName.Value;
         Parameters.CkanUrlUpdate = WebConfigSettings.DataGovCkanUpdateUrl;
         Parameters.NameValueCollection.Add(IdKey, this.TextBoxResourceId.Text);
         return Parameters;
      }
      //---------------------------------------------------------------------------------------
      void SubmitButton_Click(Object sender, EventArgs e) {
         try {
            DataGovOutcome Outcome = DataGovManager.UploadToDataGov(SetParameters());
            TextBoxOutcome.Text = Outcome.FormattedMessage;
            this.SuccessPlace.Visible = true;
            this.ErrorPlace.Visible = false;
            ;
         }
         catch (Exception ex) {
            TextBoxOutcome.Text = ex.ToString();
            this.ErrorPlace.Visible = true;
            this.SuccessPlace.Visible = false;
         }
      }
   }
}