import React, { Component } from 'react'

class TabsPaneTitle extends Component{
  constructor(props){
    super(props)
    this.state={
      isActive:false
    }
  }
  handleChangeIsActiveImgOver=()=>{

    if(this.props.isActive !== true){
      this.setState({
        isActive:true
      })

    }
  }
  handleChangeIsActiveImgOut=()=>{
    if(this.props.isActive !== true){
      this.setState({
        isActive:false
      })
    }

  }
  render(){

    return(
      <div className='tab_pane_title'>
        <p>
          <img src={this.state.isActive===false? this.props.imgUrl : this.props.imgUrlHover}
            alt={this.props.titleText}
            onMouseOver={this.handleChangeIsActiveImgOver}
            onMouseOut={this.handleChangeIsActiveImgOut}
          />
      </p>
        <p>{this.props.titleText}</p>
      </div>
    )
  }
  componentDidMount(){
    if(this.props.isActive === true){
      this.setState({
        isActive:true
      })
    }
  }
  componentWillReceiveProps(nextProps){
    // console.log(nextProps,'ss');
    this.setState({
      isActive:nextProps.isActive
    })
  }
}
export default TabsPaneTitle
