import React, { Component } from 'react'
import { Modal, Button, message } from 'antd'
const confirm = Modal.confirm
import Fetch from 'utils/fetch'

class ModalPromotion extends Component {
	constructor(props){
    super(props)
     this.state = {

  	}
		// console.log(this.props.goupUri,'kaishi');
  }

  showPromotion=()=>{
      confirm({
        title: `${this.props.btnName}`,
        content: `确定将这${this.props.id.length!==undefined? this.props.id.length : ''}
									条数据${this.props.btnName}吗？`,
        okText: '确定',
        cancelText: '取消',
        onOk:()=>{
					if(this.props.goupUri !== ''){
						this.handleChangePromotion()
					}else{
						message.error('地址不能为空')
					}
        },
        onCancel:()=>{
          message.success('已取消')
        }
      })

  }
 handleChangePromotion=()=>{
	 let formData= new FormData()
	 if(this.props.id.length !==undefined){
		 const IdLength = this.props.id.length
		 if(IdLength ===1){
			 const newId = this.props.id.toString()
			 formData.append('id',newId)
		 }else if(IdLength >1){
			 const newId = this.props.id.join('|')
				formData.append('id',newId)
		 }
	 }else{
		 formData.append('id',this.props.id)
	 }
	 Fetch.post({
		 uri:this.props.goupUri,
		 callback:(res)=>{
			 // console.log(res,'升级');
			 if(res.message && res.message==='升级成功'){
				 message.success(res.message)
				 if(this.props.RefreshTableState){
					 this.props.RefreshTableState(true)
				 }
			 }else{
				 message.error(res.message || res.error);
				 if(this.props.RefreshTableState){
					 this.props.RefreshTableState(true)
				 }
			 }
		 },
		 formData:formData
	 })
 }

  render() {
    return (
			<div>

       <Button onClick={this.showPromotion} >
       		{this.props.btnName}
      	</Button>
			</div>
    );
  }

}


export default ModalPromotion
